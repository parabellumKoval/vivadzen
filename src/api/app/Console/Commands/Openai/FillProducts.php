<?php

namespace App\Console\Commands\Openai;

// use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use \Backpack\Settings\app\Models\Settings;

class FillProducts extends BaseAi
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:fill-products {test_limits?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';


    const TEST_LIMITS = null;
    const DEFAULT_BRANDS_CHUNK_SIZE = 10;
    const DEFAULT_CATEGORIES_CHUNK_SIZE = 10;
    const DEFAULT_NAMES_CHUNK_SIZE = 10;
    const DEFAULT_MERCHANTS_CHUNK_SIZE = 10;
    
    private $test_limits = null;
    private $chunk_limit = null;

    private $lang = 'uk';
    

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
      parent::__construct();
    }

        
    /**
     * Method loadSettings
     *
     * @return void
     */
    private function loadSettings()
    {
        $settings = Settings::where('key', 'ai_generation_settings')->first();
        $this->settings = $settings ? $settings->extras : [];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

      $this->test_limits = $this->argument('test_limits') ?? self::TEST_LIMITS;

      $this->loadSettings();

      if (!isset($this->settings['auto_generation_enabled']) || !$this->settings['auto_generation_enabled']) {
          $this->info('AI generation is disabled in settings');
          return;
      }


      if ($this->settings['format_names'] ?? false) {
        $this->line('START PROCESSING NAMES...');

        $this->call('openai:format-product-names', [
          'chunk_limit' => $this->chunk_limit,
          'chunk_size' => self::DEFAULT_NAMES_CHUNK_SIZE
        ]);
      }

      if ($this->settings['generate_merchant'] ?? false) {
        $this->line('START PROCESSING MERCHANTS...');

        $this->call('openai:fill-product-merchants', [
          'chunk_limit' => $this->chunk_limit,
          'chunk_size' => self::DEFAULT_MERCHANTS_CHUNK_SIZE
        ]);
      }

      if ($this->settings['generate_description'] ?? false) {
        $this->line('START PROCESSING CONTENTS...');

        $this->call('openai:fill-product-contents', [
          'test_limits' => $this->test_limits,
        ]);
      }

      if ($this->settings['fill_characteristics'] ?? false) {
        $this->line('START PROCESSING PROPERTIES...');

        $this->call('openai:fill-product-properties', [
          'test_limits' => $this->test_limits,
        ]);
      }

      if ($this->settings['detect_category'] ?? false) {
        $this->line('START PROCESSING CATEGORIES...');

        $this->call('openai:fill-product-categories', [
          'chunk_limit' => $this->chunk_limit,
          'chunk_size' => self::DEFAULT_CATEGORIES_CHUNK_SIZE
        ]);
      }

      if ($this->settings['detect_brand'] ?? false) {
        $this->line('START PROCESSING BRANDS...');
        
        $this->call('openai:fill-product-brands', [
          'chunk_limit' => $this->chunk_limit,
          'chunk_size' => self::DEFAULT_BRANDS_CHUNK_SIZE
        ]);
      }


    }

    
   
}
