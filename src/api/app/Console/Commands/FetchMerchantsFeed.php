<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchMerchantsFeed extends Command
{
    protected $signature = 'fetch:merchants-feed';
    
    protected $description = 'Fetches XML from /merchants-feed route and saves it to public directory';

    public function handle()
    {
        try {
            // Получаем XML с локального маршрута
            $response = Http::get(config('app.url') . '/merchants-feed');
            
            if ($response->successful()) {
                // Путь для сохранения файла в public
                $filePath = 'merchants-feed.xml';
                
                // Сохраняем или заменяем файл
                Storage::disk('uploads')->put($filePath, $response->body());
                
                $this->info('XML feed successfully fetched and saved to uploads/' . $filePath);
            } else {
                $this->error('Failed to fetch XML feed. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}