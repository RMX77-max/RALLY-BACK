<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CompetidoresImport;

class CompetidorImportController extends Controller
{
    public function importar(Request $request)
    {
        //archivo Excel
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new CompetidoresImport, $request->file('archivo'));

            return response()->json([
                'success' => true,
                'message' => 'Competidores importados correctamente.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage()
            ], 500);
        }
    }
}
