<?php

namespace App\Http\Controllers;

use App\Models\ContenedorGrid;
use App\Models\Widget;
use Illuminate\Http\Request;

class ContenedorGridController extends Controller
{
    public function index()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contenedores Grid', 'url' => ''],
        ];

        $contenedores = ContenedorGrid::all();

        return view('contenedores.index', compact('contenedores', 'breadcrumb'));
    }


    public function create()
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contenedores Grid', 'url' => route('contenedores.index')],

            ['name' => 'Crear Contenedor', 'url' => ''],

        ];
        return view('contenedores.create', compact('breadcrumb'));
    }


    public function store(Request $r)
    {
        $c = ContenedorGrid::create($r->only('nombre', 'role_id'));
        return redirect()->route('contenedor.edit', $c->id);
    }


    public function edit($id)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contenedores Grid', 'url' => route('contenedores.index')],

            ['name' => 'Editar Contenedor', 'url' => ''],

        ];
        $contenedor = ContenedorGrid::with('filas.columnas')->findOrFail($id);
        return view('contenedores.edit', compact('contenedor', 'breadcrumb'));
    }

    public function conf($id)
    {
        $breadcrumb = [
            ['name' => 'Inicio', 'url' => route('home')],
            ['name' => 'Contenedores Grid', 'url' => route('contenedores.index')],
            ['name' => 'Editar Contenedor', 'url' => ''],

        ];
        $widgets = Widget::all();

        $contenedor = ContenedorGrid::with('filas.columnas')->findOrFail($id);

        return view('contenedores.conf', compact('widgets', 'contenedor', 'breadcrumb'));
    }
}
