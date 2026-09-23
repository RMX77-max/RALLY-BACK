<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ArchivoRallyController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['archivo' => 'required|file|mimes:jpg,jpeg,png,webp,pdf,gpx,kml|max:15360', 'carpeta' => 'nullable|in:banners,mapas,equipos,galeria,etapas,documentos']);
        $folder = $data['carpeta'] ?? 'documentos';
        $path = $request->file('archivo')->store($folder, 'public');
        return response()->json(['path' => $path, 'url' => asset('storage/'.$path)], 201);
    }
}
