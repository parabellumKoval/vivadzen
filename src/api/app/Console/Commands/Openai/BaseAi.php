<?php

namespace App\Console\Commands\Openai;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use OpenAI;

class BaseAi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:BaseAi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';

    protected $client = null;
    protected $settings = null;

    protected $available_languages = [];

    protected $langs_list = [];


    const CATEGORY_CHUNK_SIZE = 100;

    const TEST_CHUNK_LIMITS = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
      parent::__construct();
      $this->setClient();

      // available languages
      $this->available_languages = config('backpack.crud.locales');
      $this->langs_list = array_keys($this->available_languages);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    }

    /**
     * setClient
     *
     * @return void
     */
    protected function setClient() {
      $yourApiKey = config('openai.key');
      $this->client = OpenAI::client($yourApiKey);
    }
    
    /**
     * Method loadPromptFromFile
     *
     * @param string $filename
     * @return string
     */
    protected function loadPromptFromFile($filename)
    {
        $path = __DIR__ . '/prompts/' . $filename;
        if (!file_exists($path)) {
            throw new \RuntimeException("Prompt file not found: {$filename}");
        }
        return file_get_contents($path);
    }
    

    /**
     * getModelsList
     *
     * @return void
     */
    protected function getModelsList() {
      try {
        $response = $this->client->models()->list();
        $models = $response->data;
      }catch(\Exception $e) {
        return $e->getMessage();
      }

      $names = collect($models)->pluck('id');
      return $names;
    }


    
    /**
     * Method extractJsonFromOpenAiResponse
     *
     * @param $response $response [explicite description]
     *
     * @return array
     */
    protected function extractJsonFromOpenAiResponse($response): ?array
    {
        if (!isset($response->choices[0]->message->content)) {
            return null;
        }

        $content = trim($response->choices[0]->message->content);

        // Попробуем сразу декодировать
        $data = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }

        // Если не получилось — попытаемся вытащить JSON из текста
        if (preg_match('/\[\s*\{.*\}\s*\]/s', $content, $matches)) {
            $json = $matches[0];
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }

        // Альтернативно — если массив без скобок (реже, но вдруг)
        if (preg_match('/\{\s*"id"\s*:\s*\d+.*?\}/s', $content, $matches)) {
            $json = '[' . $matches[0] . ']';
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return $data;
            }
        }

        // Вернуть null, если не удалось декодировать
        return null;
    }

}
