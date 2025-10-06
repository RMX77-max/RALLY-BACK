<?php

namespace App\Http\Controllers;

use App\Models\Galeria;
use Illuminate\Http\Request;

class GaleriaController extends Controller
{
    public function index()
    {
        return Galeria::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'imagen' => 'required|string',
            'titulo' => 'nullable|string',
            'descripcion' => 'nullable|string'
        ]);

        $galeria = Galeria::create($request->all());
        return response()->json($galeria, 201);
    }

    public function destroy($id)
    {
        $galeria = Galeria::findOrFail($id);
        $galeria->delete();
        return response()->json(['message' => 'Imagen eliminada']);
    }
}
