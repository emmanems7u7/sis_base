<?php

namespace App\Interfaces;

interface CorreoInterface
{
    public function EditarPlantillaCorreo($request, $email);

    public function EditarConfCorreo($correoId, $request);

    public function CrearPlantilla($request);
    public function EditarPlantilla($request, $plantilla);
    public function EliminarPlantilla($plantilla);

}
