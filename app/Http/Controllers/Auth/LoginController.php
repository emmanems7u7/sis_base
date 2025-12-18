<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorCodeMail;
use App\Models\ConfCorreo;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    use \Illuminate\Foundation\Auth\AuthenticatesUsers {
        // Se expone el trait con otro alias por si se requiere
        attemptLogin as baseAttemptLogin;
    }

    /* ================================ */
    /*  MÉTODOS PERSONALIZADOS AQUÍ     */
    /* ================================ */

    /** Sobrescribe attemptLogin para limitar sesiones */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);

        if (Auth::validate($credentials)) {
            $user = Auth::getProvider()->retrieveByCredentials($credentials);

            $limite = DB::table('configuracion')->value('limite_de_sesiones');
            $sesiones = DB::table('sessions')->where('user_id', $user->id)->count();

            if ($sesiones >= $limite) {
                return false;           // Se bloquea el login
            }

            return Auth::attempt($credentials, $request->filled('remember'));
        }

        return false;
    }

    /** Sobrescribe authenticated para disparar el 2FA */
    protected function authenticated(Request $request, $user)
    {
        $conf = ConfCorreo::first();
        if (!$conf) {
            return response()->json(['error' => 'Configuración no encontrada'], 404);
        }

        if (twoFactorGlobalEnabled()) {
            $user->generateTwoFactorCode();
            Mail::to($user->email)->send(new TwoFactorCodeMail($user));

            Auth::logout();
            session(['two_factor' => $user->id]);

            return redirect()->route('verify.index');
        }

        return redirect()->intended('/home');
    }

    /** Respuesta cuando el límite se excede */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
            'limite' => ['Ha alcanzado el número máximo de sesiones activas permitidas.'],
        ]);
    }
}
