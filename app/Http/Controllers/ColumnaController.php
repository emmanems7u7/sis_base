<?php

namespace App\Http\Controllers;

use App\Models\Columna;
use Illuminate\Http\Request;

class ColumnaController extends Controller
{
    public function store(Request $r)
    {
        return Columna::create($r->all());
    }


    public function ordenar(Request $r)
    {
        foreach ($r->orden as $i => $id) {
            Columna::where('id', $id)->update(['posicion' => $i]);
        }
    }
    public function destroy($id)
    {
        Columna::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function asignarWidget(Request $request)
    {
        $columna = Columna::findOrFail($request->columna_id);
        $columna->widget_id = $request->widget_id;
        $columna->save();

        return response()->json(['success' => true]);
    }

    public function quitarWidget(Request $request)
    {
        $columna = Columna::findOrFail($request->columna_id);
        $columna->widget_id = null;
        $columna->save();

        return response()->json(['success' => true]);
    }
}
