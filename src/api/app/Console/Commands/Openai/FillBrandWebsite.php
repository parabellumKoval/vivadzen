<?php

namespace App\Console\Commands\Openai;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use App\Models\Brand;

use OpenAI;

class FillBrandWebsite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:fill-brand-website';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';

    protected $client = null;

    private $available_languages = [];

    private $langs_list = [];

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
      $this->fillBrandsWebsite();
    }

    /**
     * setClient
     *
     * @return void
     */
    private function setClient() {
      $yourApiKey = config('openai.key');
      $this->client = OpenAI::client($yourApiKey);
    }

    /**
     * fillProducts
     *
     * @return void
     */
    private function fillBrandsWebsite() {

      $langs_list = $this->langs_list;

	    $brands = Brand::whereNull('extras->website')
               ->orWhere('extras->website', '=', '')
               ->get();

      $bar = $this->output->createProgressBar($brands->count());
      $bar->start();

      foreach($brands as $index => $brand) {
        // if ( $index >= 2) break;

        $response = $this->getWebsite($brand);
        $url = $this->extractAndConvertLink($response);

        if(!$url) {
          continue;
        }

        $brand->website = $url;
        $brand->save();

        $this->info('Brand: ' . $brand->name . ' - Website: ' . $url);
        $bar->advance();
      }

      $bar->finish();
    }

    
    /**
     * Method getWebsite
     *
     * @param $brand $brand [explicite description]
     *
     * @return void
     */
    private function getWebsite($brand = null) {

      if(!$brand || empty($brand->name)) {
        $this->error('Brand not found or name is empty');
        return null;
      }

      // $prompt = 'Provide only the official website URL for the brand "' . $brand->name . '" in plain text. No introduction, no formatting, no additional text. Just the URL like https://example.com.';
      
      $prompt = 'Find and return ONLY the official website URL of the brand "' . $brand->name . '". 
        Prefer international domains such as .com, .net, .org, .global, .io, etc.
        If an international version does not exist, return the official regional domain (e.g., .pl, .de).
        Return ONLY the plain URL in this format: https://example.com. No additional text or formatting.';

          
      $result = $this->client->chat()->create([
        'model' => 'gpt-4o',
        'messages' => [
          [
            'role' => 'system',
            'content' => $prompt
          ]
        ],
      ]);

      return $result->choices[0]->message->content ?? null;
    }
    
    /**
     * Method extractAndConvertLink
     *
     * @param string $text [explicite description]
     *
     * @return string
     */
    private function extractAndConvertLink(string $text): string
    {
        // Регулярное выражение для поиска URL в тексте
        $pattern = '/https?:\/\/[^\s]+/i';
        preg_match($pattern, $text, $matches);
        
        // Если ссылка не найдена, возвращаем пустую строку или исходный текст
        if (empty($matches)) {
            return '';
        }
        
        $originalUrl = $matches[0];
        $parsedUrl = parse_url($originalUrl);
        
        if (!isset($parsedUrl['host'])) {
            return $originalUrl;
        }
        
        $originalDomain = $parsedUrl['host'];
        $path = $parsedUrl['path'] ?? '';
        
        // Список международных доменов для проверки
        $internationalTlds = ['.com', '.global', '.org', '.net', '.info'];
        
        // Извлекаем базовое имя домена (без регионального TLD)
        $domainParts = explode('.', $originalDomain);
        $tld = end($domainParts);
        $baseDomain = str_replace(".$tld", '', $originalDomain);
        
        // Проверяем международные версии
        foreach ($internationalTlds as $newTld) {
            $newDomain = $baseDomain . $newTld;
            $newUrl = $parsedUrl['scheme'] . '://' . $newDomain . $path;
            
            try {
                // Проверяем доступность сайта
                $response = Http::timeout(5)->get($newUrl);
                
                if ($response->successful()) {
                    return $newUrl;
                }
            } catch (\Exception $e) {
                // Продолжаем проверку следующего TLD в случае ошибки
                continue;
            }
        }
        
        // Если международная версия не найдена или не работает, возвращаем оригинальную ссылку
        return $originalUrl;
    }

}
