<?php

namespace App\Jobs;

use App\Models\CamposForm;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;

class ImportarFormularioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $form;
    protected $archivoPath;

    public function __construct($form, $archivoPath)
    {
        $this->form = $form;
        $this->archivoPath = $archivoPath;
    }

    public function handle()
    {
        $lineas = file($this->archivoPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lineas))
            return;

        $campos = CamposForm::where('form_id', $this->form)->get();
        $nombresCampos = $campos->pluck('nombre')->toArray();

        // Validar encabezado
        $primeraLinea = str_getcsv(array_shift($lineas), ',');
        if ($primeraLinea !== $nombresCampos) {
            Log::warning("Encabezado del archivo no coincide con los campos del formulario.");
            return;
        }

        $batchSize = 1000; // registros por lote
        $totalCargados = 0;

        // Dividir en chunks
        $lineasChunks = array_chunk($lineas, $batchSize);

        foreach ($lineasChunks as $chunk) {
            DB::transaction(function () use ($chunk, $campos, &$totalCargados) {
                foreach ($chunk as $linea) {
                    $datos = str_getcsv($linea, ',');
                    if (count($datos) !== count($campos))
                        continue;

                    $dataAsociativa = [];
                    foreach ($campos as $index => $campo) {
                        $dataAsociativa[$campo->nombre] = $datos[$index] ?? null;
                    }

                    $respuesta = app('App\Repositories\FormularioRepository')->crearRespuesta($this->form);

                    foreach ($campos as $campo) {
                        $valor = $dataAsociativa[$campo->nombre] ?? null;
                        if ($valor === null)
                            continue;

                        $tipo = strtolower($campo->campo_nombre);
                        if (in_array($tipo, ['imagen', 'video', 'archivo'])) {
                            app('App\Repositories\FormularioRepository')->guardarArchivoGenerico($campo, $respuesta->id, $this->form, $valor);
                        } else {
                            app('App\Repositories\FormularioRepository')->guardarValorSimple($campo, $respuesta->id, $valor);
                        }
                    }

                    $totalCargados++;
                }
            });

            // Aquí podrías guardar progreso en BD
            // Progress::update($totalCargados);
        }

        Log::info("Importación completada. Total registros cargados: {$totalCargados}");
    }
}
