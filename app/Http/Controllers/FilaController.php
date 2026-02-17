<?php

namespace App\Http\Controllers;

use App\Models\Fila;
use Illuminate\Http\Request;

class FilaController extends Controller
{
    public function store(Request $r)
    {
        return Fila::create([
            'contenedor_grid_id' => $r->contenedor_id,
            'posicion' => Fila::where('contenedor_grid_id', $r->contenedor_id)->count(),
        ]);
    }


    public function ordenar(Request $r)
    {
        foreach ($r->orden as $i => $id) {
            Fila::where('id', $id)->update(['posicion' => $i]);
        }
    }
    public function destroy($id)
    {
        Fila::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
