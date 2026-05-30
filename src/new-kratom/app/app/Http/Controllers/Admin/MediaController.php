<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateImageRenditions;
use App\Models\Media;
use App\Services\MediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(private readonly MediaStorage $storage) {}

    public function index(Request $request): JsonResponse
    {
        $items = Media::latest()->paginate($request->integer('per_page', 40));

        $items->getCollection()->transform(function (Media $m) {
            $m->setAttribute('url', $this->storage->url($m->disk ?: 'public', $m->path));
            return $m;
        });

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $maxKb = (int) config('media.image_max_kb', 10240);
        $mimes = implode(',', config('media.image_mimes', ['jpg', 'jpeg', 'png', 'webp', 'avif']));

        $request->validate([
            'file' => "required|file|mimes:{$mimes}|max:{$maxKb}",
            'alt' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $stored = $this->storage->store($file, 'media');

        $media = Media::create([
            'disk' => $stored['disk'],
            'path' => $stored['path'],
            'filename' => $stored['filename'],
            'mime' => $stored['mime'],
            'size' => $stored['size'],
            'alt' => $request->input('alt'),
            'uploaded_by' => $request->user()->id,
        ]);

        if ($media->disk === 'public') {
            GenerateImageRenditions::dispatch($media->id);
        }

        $media->setAttribute('url', $stored['url']);

        return response()->json(['data' => $media], 201);
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->storage->delete($media->disk ?: 'public', $media->path);
        $media->delete();

        return response()->json(['ok' => true]);
    }
}
