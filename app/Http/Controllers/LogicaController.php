<?php

namespace App\Http\Controllers;

use App\Models\RespuestasForm;
use Illuminate\Http\Request;
use App\Interfaces\FormLogicInterface;
use Illuminate\Support\Facades\Log;

class LogicaController extends Controller
{

    protected $FormLogicInterface;
    public function __construct(

        FormLogicInterface $formLogicInterface
    ) {

        $this->FormLogicInterface = $formLogicInterface;


    }
    public function ejecutar(Request $request)
    {
        Log::info('🔥 ENTRO A ejecutar-logica-formulario', $request->all());
        ignore_user_abort(true);
        set_time_limit(0);

        $respuestaId = $request->respuesta_id;

        $respuesta = RespuestasForm::find($respuestaId);

        $this->FormLogicInterface->ejecutarLogica($respuesta, $request->filas, $request->evento);

        return response()->json(['ok' => true]);
    }
}
