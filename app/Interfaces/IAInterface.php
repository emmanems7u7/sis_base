<?php

namespace App\Interfaces;

interface IAInterface
{
    public function consultarIA($consulta, $instruccion, $maxTokens, $temperature, $tipoConsulta = 'general');


}
