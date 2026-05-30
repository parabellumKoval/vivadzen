<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Эндпоинт для WYSIWYG-редактора (TipTap): принимает картинку,
 * кладёт её через MediaStorage (local | bunny) и возвращает URL,
 * который редактор вставляет тегом <img src>.
 *
 * Ответ намеренно простой: { url, filename, mime, size }
 */
class EditorMediaController extends Controller
{
    public function __construct(private readonly MediaStorage $storage) {}

    public function image(Request $request): JsonResponse
    {
        $maxKb = (int) config('media.image_max_kb', 10240);
        $mimes = implode(',', config('media.image_mimes', ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif']));

        $request->validate([
            'file' => "required|file|mimes:{$mimes}|max:{$maxKb}",
            'folder' => 'nullable|string|max:64',
        ]);

        $folder = trim((string) $request->input('folder', 'editor'), '/');
        if ($folder === '' || ! preg_match('/^[a-zA-Z0-9_\-\/]+$/', $folder)) {
            $folder = 'editor';
        }

        $stored = $this->storage->store($request->file('file'), $folder);

        return response()->json([
            'data' => [
                'url' => $stored['url'],
                'filename' => $stored['filename'],
                'mime' => $stored['mime'],
                'size' => $stored['size'],
                'disk' => $stored['disk'],
                'path' => $stored['path'],
            ],
        ], 201);
    }
}
