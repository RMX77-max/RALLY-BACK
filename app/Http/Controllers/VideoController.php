<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index()
    {
        return Video::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
            'titulo' => 'nullable|string',
            'descripcion' => 'nullable|string'
        ]);

        $video = Video::create($request->all());
        return response()->json($video, 201);
    }

    public function destroy($id)
    {
        $video = Video::findOrFail($id);
        $video->delete();
        return response()->json(['message' => 'Video eliminado']);
    }
}
