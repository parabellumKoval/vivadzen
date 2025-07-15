<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchMerchantsFeed extends Command
{
    protected $signature = 'fetch:merchants-feed';
    
    protected $description = 'Fetches XML from /merchants-feed route and saves it to public directory';

    protected $google_langs = ['ru', 'uk'];
    protected $facebook_langs = ['uk'];

    public function handle()
    {
        foreach($this->google_langs as $lang) {
            $postfix = $lang === 'uk'? null: $lang;
            $this->createFeedXml($postfix, 'merchants-feed');
        }

        foreach($this->facebook_langs as $lang) {
            $postfix = $lang === 'uk'? null: $lang;
            $this->createFeedXml($postfix, 'facebook-feed');
        }
    }

    private function createFeedXml($postfix = null, $feed_url = '') {
        $base_url = config('app.url') . '/' . $feed_url;
        $full_url = $postfix? $base_url . '-' . $postfix: $base_url;

        $filename = $feed_url;
        $file_ext = '.xml';
        
        // Путь для сохранения файла в public
        $full_filepath = $postfix? $filename . '-' . $postfix . $file_ext: $filename.$file_ext;

        try {
            // Получаем XML с локального маршрута
            $response = Http::get($full_url);
            
            if ($response->successful()) {
                // Сохраняем или заменяем файл
                Storage::disk('uploads')->put($full_filepath, $response->body());
                
                $this->info('XML feed successfully fetched and saved to uploads/' . $full_filepath);
            } else {
                $this->error('Failed to fetch XML feed from '. $full_url .'. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}