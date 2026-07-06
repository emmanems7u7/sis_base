<?php

namespace App\Http\Controllers;

use App\Models\FormConfiguration;
use Illuminate\Http\Request;
use App\Interfaces\FormConfigInterface;

class FormConfigurationController extends Controller
{

    protected $formConfigInterface;
    public function __construct(
        FormConfigInterface $formConfigInterface,

    ) {

        $this->formConfigInterface = $formConfigInterface;



    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $formularioId)
    {

        $config = FormConfiguration::firstOrCreate(['formulario_id' => $formularioId], ['config' => []]);

        $config->update(['config' => $this->formConfigInterface->buildConfig($request)]);

        $this->formConfigInterface->clear($formularioId);

        return redirect()->back()->with('status', 'Configuración actualizada correctamente');
    }


}
