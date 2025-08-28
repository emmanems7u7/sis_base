<?php

namespace App\Interfaces;

interface IAInterface
{
    public function consultarIA($mensajeActual, $instruccion, $maxTokens, $temperature, $tipoConsulta = 'general', $anonId = null);


}
