<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class FetchImage extends Command
{
    protected $signature = 'fetch:image {url} {output}';
    protected $description = 'Fetch an image from a URL using proxy server and save it locally';

    public function handle()
    {
        $url = $this->argument('url'); // URL изображения
        $outputPath = $this->argument('output'); // Путь для сохранения

        // Создание клиента Guzzle
        $client = new Client([
            // 'base_uri' => 'http://localhost:3000', // Адрес прокси-сервера
            'base_uri' => 'proxy:3000', // Адрес прокси-сервера
            'timeout' => 60, // Увеличенный таймаут для Puppeteer
        ]);

        try {
            // Отправка запроса к прокси-серверу
            $response = $client->post('/fetch', [
                'json' => ['url' => $url],
            ]);

            // Проверка статуса ответа
            if ($response->getStatusCode() === 200) {
                // Сохранение изображения
                file_put_contents($outputPath, $response->getBody());
                $this->info("Image saved to {$outputPath}");
            } else {
                $this->error("Failed to download image. Status code: " . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            $this->error("Error downloading image: " . $e->getMessage());
        }
    }
}