<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CorreoDinamico extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectText;
    public string $bodyHtml;

    public function __construct(string $subjectText, string $bodyHtml)
    {
        $this->subjectText = $this->reemplazarVariables($subjectText);
        $this->bodyHtml = $this->reemplazarVariables($bodyHtml);
    }

    public function build()
    {

        return $this->subject($this->subjectText)
            ->html($this->bodyHtml);
    }

    /**
     * Reemplaza variables dinámicas tipo [fecha_actual]
     */
    private function reemplazarVariables(string $texto): string
    {
        // Zona horaria Bolivia
        $now = Carbon::now('America/La_Paz');

        $variables = [
            '[fecha_actual]' => $now->format('d/m/Y'),
            '[hora_actual]' => $now->format('H:i:s'),
            '[fecha_hora_actual]' => $now->format('d/m/Y H:i:s'),
            '[dia_actual]' => $now->format('d'),
            '[anio_actual]' => $now->format('Y'),
            '[mes_actual]' => $now->translatedFormat('F'), // diciembre
            '[nombre_sistema]' => config('app.name'),
            '[usuario_actual]' => Auth::user()->NombreCompleto ?? 'Usuario',
        ];

        return str_replace(
            array_keys($variables),
            array_values($variables),
            $texto
        );
    }
}
