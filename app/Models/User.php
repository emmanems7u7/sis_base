<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'foto_perfil',
        'email',
        'password',
        'usuario_fecha_ultimo_acceso',
        'usuario_fecha_ultimo_password',
        'usuario_nombres',
        'usuario_app',
        'usuario_apm',
        'usuario_telefono',
        'usuario_direccion',
        'accion_fecha',
        'accion_usuario',
        'usuario_activo',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_expires_at' => 'datetime',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function generateTwoFactorCode()
    {
        $this->two_factor_code = rand(100000, 999999);
        $this->two_factor_expires_at = now()->addMinutes(10);
        $this->save();
    }

    public function resetTwoFactorCode()
    {
        $this->two_factor_code = null;
        $this->two_factor_expires_at = null;
        $this->save();
    }
    public function preferences()
    {
        return $this->hasOne(UserPersonalizacion::class);
    }
    public function preferredNotificationChannels()
    {
        //  lógica para determinar los canales preferidos por usuario
        if ($this->preferenciaNotificacion === 'mail') {
            return ['mail'];
        } else {
            return ['database'];
        }
    }
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->usuario_nombres} {$this->usuario_app} {$this->usuario_apm}";
    }

}
