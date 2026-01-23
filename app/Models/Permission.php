<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{

    public function esDinamico(): bool
    {
        return $this->dinamico === 1;
    }

    public function nombreBase()
    {
        return explode('.', $this->name)[0];
    }


    /**
     * Devuelve el nombre para mostrar en la vista
     */
    public function nombreParaVista(): string
    {
        // Si no es dinámico, solo mostrar el name
        if (!$this->esDinamico()) {
            return $this->name;
        }

        // Es dinámico: name = "2.ver"
        [$form_id, $accion] = explode('.', $this->name);

        // Buscar el formulario por id
        $formulario = Formulario::find($form_id);

        $nombreForm = $formulario ? $formulario->nombre : $form_id;

        return "{$nombreForm}.{$accion}";
    }

    /**
     * Devuelve el id del formulario si es dinámico
     */
    public function formularioId(): ?int
    {
        if ($this->esDinamico()) {
            return (int) explode('.', $this->name)[0];
        }
        return null;
    }

    /**
     * Devuelve la acción del permiso
     */
    public function accion(): string
    {
        return explode('.', $this->name)[1] ?? $this->name;
    }
}