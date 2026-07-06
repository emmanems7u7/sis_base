<?php

namespace App\Interfaces;

interface FormConfigInterface
{
    public function get($formularioId);
    public function value($formularioId, $key);
    public function clear($formularioId);
    public function buildConfig($request);
    public function defaultFields($config);
}
