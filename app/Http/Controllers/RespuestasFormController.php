<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RespuestasForm;
use App\Models\RespuestasCampo;
use App\Models\CamposForm;
use App\Models\Formulario;
use Illuminate\Support\Facades\DB;
use App\Models\Catalogo;
use App\Interfaces\CatalogoInterface;

class RespuestasFormController extends Controller
{


    protected $CatalogoRepository;
    public function __construct(CatalogoInterface $catalogoInterface)
    {

        $this->CatalogoRepository = $catalogoInterface;

    }
    public function index()
    {


        $respuestas = RespuestasForm::with(['camposRespuestas.campo', 'actor', 'formulario'])->get();

        return view('formularios.respuestas', compact('respuestas'));
    }

    public function indexPorFormulario(Request $request, $form)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Respuestas ', 'url' => route('formularios.index')],
        ];

        $formulario = Formulario::with('campos.opciones_catalogo')->findOrFail($form);

        $query = $formulario->respuestas()->with('camposRespuestas.campo', 'actor');

        // Filtro de búsqueda por nombre del actor
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('actor', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Paginación
        $respuestas = $query->orderBy('created_at', 'desc')->paginate(5)->withQueryString();

        return view('formularios.respuestas_formulario', compact('formulario', 'respuestas', 'breadcrumb'));
    }

    public function create($form)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Respuestas Formulario', 'url' => route('formularios.respuestas.formulario', $form)],

            ['name' => 'Registrar Datos ', 'url' => route('permissions.index')],
        ];

        $formulario = Formulario::with([
            'campos' => function ($q) {
                $q->orderBy('posicion');
            }
        ])->findOrFail($form);

        return view('formularios.registrar_datos_form', compact('formulario', 'breadcrumb'));
    }




    public function store(Request $request, $form)
    {
        // 1️⃣ Obtener los campos del formulario
        $campos = CamposForm::where('form_id', $form)->get();


        // 2️⃣ Construir reglas dinámicas
        $rules = $this->validacion($campos);


        // 3️⃣ Validar los datos
        $validatedData = $request->validate($rules);

        // 4️⃣ Validar que las opciones enviadas existan en el catálogo
        foreach ($campos as $campo) {
            $tipo = strtolower($campo->campo_nombre);
            $name = $campo->nombre;

            if (in_array($tipo, ['checkbox', 'radio', 'selector']) && $request->has($name)) {
                $valores = is_array($request->input($name)) ? $request->input($name) : [$request->input($name)];
                $opcionesValidas = $campo->opciones_catalogo->pluck('catalogo_codigo')->toArray();

                foreach ($valores as $v) {
                    if (!in_array($v, $opcionesValidas)) {
                        return redirect()->back()
                            ->withErrors("El valor '$v' no es válido para el campo '{$campo->etiqueta}'.")
                            ->withInput();
                    }
                }
            }
        }

        // 5️⃣ Guardar todo dentro de una transacción
        DB::beginTransaction();
        try {
            $respuesta = RespuestasForm::create([
                'form_id' => $form,
                'actor_id' => auth()->id() ?? null,
            ]);

            foreach ($campos as $campo) {
                $name = $campo->nombre;
                $tipo = strtolower($campo->campo_nombre);
                $valor = null;

                // 📁 Manejo de archivos (imagen, video, archivo)
                if (in_array($tipo, ['imagen', 'video', 'archivo']) && $request->hasFile($name)) {
                    $file = $request->file($name);
                    $filename = uniqid($tipo . '_') . '.' . $file->getClientOriginalExtension();
                    $path = match ($tipo) {
                        'imagen' => public_path("archivos/formulario_{$form}/imagenes"),
                        'video' => public_path("archivos/formulario_{$form}/videos"),
                        'archivo' => public_path("archivos/formulario_{$form}/archivos"),
                    };
                    if (!file_exists($path))
                        mkdir($path, 0777, true);
                    $file->move($path, $filename);
                    $valor = $filename;
                } elseif ($request->has($name)) {
                    $valor = $request->input($name);
                }

                // 📌 Guardado en base de datos
                if ($valor !== null) {
                    if (is_array($valor)) {
                        foreach ($valor as $v) {
                            RespuestasCampo::create([
                                'respuesta_id' => $respuesta->id,
                                'cf_id' => $campo->id,
                                'valor' => $v,
                            ]);
                        }
                    } else {
                        RespuestasCampo::create([
                            'respuesta_id' => $respuesta->id,
                            'cf_id' => $campo->id,
                            'valor' => $valor,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('formularios.respuestas.formulario', $form)
                ->with('status', 'Formulario enviado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Error al guardar el formulario: ' . $e->getMessage());
        }
    }

    public function edit(RespuestasForm $respuesta)
    {

        $formulario = $respuesta->formulario()->with('campos')->first();
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Formularios', 'url' => route('formularios.index')],
            ['name' => 'Respuestas Formulario', 'url' => route('formularios.respuestas.formulario', $formulario)],

            ['name' => 'Editar Datos ', 'url' => route('permissions.index')],
        ];

        // Cargar las opciones de catálogo para cada campo
        $campos = $formulario->campos->sortBy('posicion')->map(function ($campo) {
            $campo->opciones_catalogo = $campo->categoria_id
                ? Catalogo::where('categoria_id', $campo->categoria_id)->get()
                : collect([]);
            return $campo;
        });

        return view('formularios.editar_datos_form', compact('breadcrumb', 'respuesta', 'formulario', 'campos'));
    }

    public function update(Request $request, RespuestasForm $respuesta)
    {
        $form = $respuesta->form_id;

        // 1️⃣ Obtener los campos del formulario
        $campos = CamposForm::where('form_id', $form)->get();

        // 2️⃣ Construir reglas dinámicas
        $rules = $this->validacion($campos, $respuesta->id);
        // 3️⃣ Validar los datos
        //dd($request, $rules);

        $validatedData = $request->validate($rules);

        // 4️⃣ Validar opciones de catálogo
        foreach ($campos as $campo) {
            $tipo = strtolower($campo->campo_nombre);
            $name = $campo->nombre;

            if (in_array($tipo, ['checkbox', 'radio', 'selector']) && $request->has($name)) {
                $valores = is_array($request->input($name)) ? $request->input($name) : [$request->input($name)];
                $opcionesValidas = $campo->opciones_catalogo->pluck('catalogo_codigo')->toArray();

                foreach ($valores as $v) {
                    if (!in_array($v, $opcionesValidas)) {
                        return redirect()->back()
                            ->withErrors("El valor '$v' no es válido para el campo '{$campo->etiqueta}'.")
                            ->withInput();
                    }
                }
            }
        }

        // 5️⃣ Guardar dentro de transacción
        DB::beginTransaction();
        try {

            foreach ($campos as $campo) {
                $name = $campo->nombre;
                $tipo = strtolower($campo->campo_nombre);

                // Obtener valor existente
                $old = RespuestasCampo::where('respuesta_id', $respuesta->id)
                    ->where('cf_id', $campo->id)
                    ->first();

                $valor = null;

                if (in_array($tipo, ['imagen', 'video', 'archivo'])) {
                    if ($request->hasFile($name)) {
                        // Subir nuevo archivo y eliminar el anterior
                        if ($old && $old->valor) {
                            $oldPath = match ($tipo) {
                                'imagen' => public_path("archivos/formulario_{$form}/imagenes/{$old->valor}"),
                                'video' => public_path("archivos/formulario_{$form}/videos/{$old->valor}"),
                                'archivo' => public_path("archivos/formulario_{$form}/archivos/{$old->valor}"),
                            };
                            if (file_exists($oldPath))
                                unlink($oldPath);
                        }

                        $file = $request->file($name);
                        $filename = uniqid($tipo . '_') . '.' . $file->getClientOriginalExtension();
                        $path = match ($tipo) {
                            'imagen' => public_path("archivos/formulario_{$form}/imagenes"),
                            'video' => public_path("archivos/formulario_{$form}/videos"),
                            'archivo' => public_path("archivos/formulario_{$form}/archivos"),
                        };
                        if (!file_exists($path))
                            mkdir($path, 0777, true);
                        $file->move($path, $filename);

                        $valor = $filename;
                    } elseif ($old) {
                        // Mantener valor anterior si no hay archivo nuevo
                        $valor = $old->valor;
                    }
                } else {
                    if ($request->has($name)) {
                        $valor = $request->input($name);
                    }
                }

                // Guardar o actualizar
                if ($valor !== null) {
                    if (is_array($valor)) {
                        foreach ($valor as $v) {
                            if ($old) {
                                $old->update(['valor' => $v]);
                            } else {
                                RespuestasCampo::create([
                                    'respuesta_id' => $respuesta->id,
                                    'cf_id' => $campo->id,
                                    'valor' => $v,
                                ]);
                            }
                        }
                    } else {
                        if ($old) {
                            $old->update(['valor' => $valor]);
                        } else {
                            RespuestasCampo::create([
                                'respuesta_id' => $respuesta->id,
                                'cf_id' => $campo->id,
                                'valor' => $valor,
                            ]);
                        }
                    }
                }
            }



            DB::commit();
            return redirect()->route('formularios.respuestas.formulario', $form)
                ->with('status', 'Respuesta actualizada correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Error al actualizar la respuesta: ' . $e->getMessage());
        }
    }
    public function validacion($campos, $respuestaId = null)
    {
        $rules = [];

        foreach ($campos as $campo) {
            $tipo = strtolower($campo->campo_nombre);
            $required = $campo->requerido ? 'required' : 'nullable';

            switch ($tipo) {
                case 'text':
                case 'textarea':
                    $rules[$campo->nombre] = [$required, 'string', 'max:255'];
                    break;

                case 'number':
                    $rules[$campo->nombre] = [$required, 'numeric'];
                    break;

                case 'checkbox':
                    $arrayRules = [$required, 'array'];
                    if ($campo->requerido)
                        $arrayRules[] = 'min:1';
                    $rules[$campo->nombre] = $arrayRules;
                    break;

                case 'radio':
                case 'selector':
                    $rules[$campo->nombre] = [$required];
                    break;

                case 'archivo':
                case 'imagen':
                case 'video':

                    $extensiones_permitidas = $this->CatalogoRepository->obtenerCatalogosPorCategoriaID($campo->categoria_id, true);
                    $extensiones = $extensiones_permitidas->pluck('catalogo_descripcion')->filter()->toArray();
                    $extensionesStr = !empty($extensiones) ? implode(',', $extensiones) : '';

                    $fileRules = [$required, 'file', 'max:50240']; // 10 MB
                    if (!empty($extensionesStr)) {
                        $fileRules[] = 'mimes:' . $extensionesStr;


                    }

                    $rules[$campo->nombre] = $fileRules;
                    break;

                case 'color':
                    $rules[$campo->nombre] = [$required, 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'];
                    break;

                case 'email':
                    $uniqueRule = $respuestaId
                        ? "unique:respuestas_campos,valor,{$respuestaId},respuesta_id,cf_id,{$campo->id}"
                        : "unique:respuestas_campos,valor,NULL,id,cf_id,{$campo->id}";
                    $rules[$campo->nombre] = [$required, 'email', 'max:255', $uniqueRule];
                    break;

                case 'password':
                    $rules[$campo->nombre] = [$required, 'string', 'min:6', 'max:255'];
                    break;

                case 'enlace':
                    $rules[$campo->nombre] = [$required, 'url'];
                    break;

                case 'fecha':
                    $rules[$campo->nombre] = [$required, 'date'];
                    break;

                case 'hora':
                    $rules[$campo->nombre] = [$required, 'date_format:H:i'];
                    break;

                default:
                    $rules[$campo->nombre] = [$required];
            }
        }
        return $rules;


    }


    public function destroy(RespuestasForm $respuesta)
    {
        // Recorrer los campos de la respuesta
        foreach ($respuesta->camposRespuestas as $campo) {
            $tipo = strtolower($campo->campo->campo_nombre ?? ''); // Asegúrate de tener la relación campo
            $valor = $campo->valor;

            if (in_array($tipo, ['imagen', 'video', 'archivo']) && $valor) {
                $path = match ($tipo) {
                    'imagen' => public_path("archivos/formulario_{$respuesta->form_id}/imagenes/{$valor}"),
                    'video' => public_path("archivos/formulario_{$respuesta->form_id}/videos/{$valor}"),
                    'archivo' => public_path("archivos/formulario_{$respuesta->form_id}/archivos/{$valor}"),
                    default => null,
                };

                if ($path && file_exists($path)) {
                    unlink($path);
                }
            }
        }

        // Borrar registros de campos y respuesta principal
        $respuesta->camposRespuestas()->delete();
        $respuesta->delete();

        return redirect()->back()->with('status', 'Respuesta eliminada correctamente.');
    }


}
