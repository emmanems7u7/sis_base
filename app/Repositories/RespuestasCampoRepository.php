<?php

namespace App\Repositories;

use App\Interfaces\RespuestasCampoInterface;
use App\Models\RespuestasCampo;

class RespuestasCampoRepository implements RespuestasCampoInterface
{
    public function GetRespCampoByIdValor($campoId, $valor)
    {
        return RespuestasCampo::where('cf_id', $campoId)
            ->where('valor', $valor)
            ->first();
    }
}
