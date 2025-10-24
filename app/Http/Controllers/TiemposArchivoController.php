<?php

namespace App\Http\Controllers;

use App\Models\TiemposArchivo;
use App\Support\ExcelReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TiemposArchivoController extends Controller
{
    // GET /api/tiempos/archivos  (pública)
    public function index(Request $request)
    {
        return TiemposArchivo::orderByDesc('id')
            ->get(['id','nombre_original','path','total_filas','created_at']);
    }

    // POST /api/tiempos/archivos  (protegida con token)
    public function store(Request $request)
    {
        // if ($request->user()?->role !== 'admin') abort(403, 'Solo admin');

        $request->validate([
            'archivo' => 'required|file|max:10240|mimes:xlsx,xls,csv',
            'first_row_header' => 'nullable|boolean',
        ]);

        $file = $request->file('archivo');
        $nombreOriginal = $file->getClientOriginalName();

        $path = $file->store("public/tiempos");
        $fullPath = Storage::path($path);

        $firstRowHeader = $request->boolean('first_row_header', true);

        $parsed = ExcelReader::readAsDisplayed($fullPath, $firstRowHeader);

        $registro = TiemposArchivo::create([
            'user_id'         => optional($request->user())->id,
            'nombre_original' => $nombreOriginal,
            'path'            => $path,
            'columnas'        => $parsed['columnas'],
            'filas'           => $parsed['filas'],
            'total_filas'     => $parsed['total_filas'],
        ]);

        return response()->json([
            'id'        => $registro->id,
            'nombre'    => $registro->nombre_original,
            'columnas'  => $parsed['columnas'],
            'filas'     => $parsed['filas'],
            'total'     => $parsed['total_filas'],
            'message'   => 'Archivo subido y leído correctamente.',
        ], 201);
    }

    // GET /api/tiempos/archivos/{id}  (pública)
    public function show(Request $request, int $id)
    {
        $r = TiemposArchivo::findOrFail($id);
        return response()->json([
            'id'        => $r->id,
            'nombre'    => $r->nombre_original,
            'columnas'  => $r->columnas,
            'filas'     => $r->filas,
            'total'     => $r->total_filas,
            'created_at'=> $r->created_at,
        ]);
    }

    // DELETE /api/tiempos/archivos/{id}  (protegida con token)
    public function destroy(Request $request, int $id)
    {
        // if ($request->user()?->role !== 'admin') abort(403, 'Solo admin');

        $r = TiemposArchivo::findOrFail($id);
        if ($r->path && Storage::exists($r->path)) {
            Storage::delete($r->path);
        }
        $r->delete();

        return response()->json(['message' => 'El archivo fue eliminado.']);
    }
}
