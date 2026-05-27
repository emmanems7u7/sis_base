<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected static $formulariosCache = null;

    public function esDinamico(): bool
    {
        return $this->dinamico === 1;
    }

    public function nombreBase()
    {
        return explode('.', $this->name)[0];
    }

    public function nombreParaVista(): string
    {
        // No dinámico
        if (!$this->esDinamico()) {
            return $this->name;
        }

        [$form_id, $accion] = explode('.', $this->name);

        // Cache memoria
        if (static::$formulariosCache === null) {

            static::$formulariosCache = Formulario::pluck('nombre', 'id');
        }

        $nombreForm = static::$formulariosCache[$form_id] ?? $form_id;

        return "{$nombreForm}.{$accion}";
    }

    public function formularioId(): ?int
    {
        if ($this->esDinamico()) {
            return (int) explode('.', $this->name)[0];
        }

        return null;
    }

    public function accion(): string
    {
        return explode('.', $this->name)[1] ?? $this->name;
    }
}