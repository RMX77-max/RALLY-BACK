<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evento;

class EventoController extends Controller
{
    /**
     * Listar todos los eventos.
     */
    public function index()
    {
        $eventos = Evento::orderBy('fecha', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $eventos
        ]);
    }

    /**
     * Crear un nuevo evento.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'ubicacion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_evento' => 'required|in:simple,grande', // 👈 validamos el tipo
        ]);

        $evento = Evento::create($request->only(
            'nombre',
            'fecha',
            'ubicacion',
            'descripcion',
            'tipo_evento'
        ));

        return response()->json([
            'success' => true,
            'data' => $evento,
            'message' => 'Evento creado correctamente'
        ], 201);
    }

    /**
     * Mostrar un evento específico.
     */
    public function show($id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $evento
        ]);
    }

    /**
     * Actualizar un evento.
     */
    public function update(Request $request, $id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado'
            ], 404);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'fecha' => 'sometimes|required|date',
            'ubicacion' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo_evento' => 'sometimes|required|in:simple,grande',
        ]);

        $evento->update($request->only(
            'nombre',
            'fecha',
            'ubicacion',
            'descripcion',
            'tipo_evento'
        ));

        return response()->json([
            'success' => true,
            'message' => "Evento actualizado correctamente",
            'data' => $evento
        ]);
    }

    /**
     * Eliminar un evento.
     */
    public function destroy($id)
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado'
            ], 404);
        }

        $evento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evento eliminado correctamente'
        ]);
    }
}
