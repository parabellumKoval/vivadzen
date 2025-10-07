<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TranslationController extends Controller
{
    static function translate($text, $source, $target)
    {
        // $text = $request->input('text');
        // $source = $request->input('source', 'ru');
        // $target = $request->input('target', 'en');

        $response = Http::post(config('services.libretranslate.url'), [
            'q' => $text,
            'source' => $source,
            'target' => $target,
            // 'api_key' => config('services.libretranslate.api_key'),
        ]);

        if ($response->successful()) {
            return response()->json([
                'translated' => $response->json()['translatedText'],
            ]);
        }

        return response()->json(['error' => 'Translation failed'], 500);
    }
}