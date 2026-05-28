<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateImageRenditions;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Media::latest()->paginate($request->integer('per_page', 40)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,avif|max:10240',
            'alt' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('products', 'public');

        $media = Media::create([
            'disk' => 'public',
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt' => $request->input('alt'),
            'uploaded_by' => $request->user()->id,
        ]);

        // Тяжёлая операция — в очередь. Фронт сразу получает media-запись,
        // renditions доступны после успешного завершения job-а.
        GenerateImageRenditions::dispatch($media->id);

        return response()->json(['data' => $media], 201);
    }

    public function destroy(Media $media): JsonResponse
    {
        if (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }
        $media->delete();
        return response()->json(['ok' => true]);
    }
}
