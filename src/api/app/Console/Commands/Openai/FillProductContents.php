<?php

namespace App\Console\Commands\Openai;

// use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;


use App\Models\Prompt;
use App\Models\Product;

use App\Models\AiGenerationHistory;
use \Backpack\Settings\app\Models\Settings;

use App\Console\Commands\Openai\Traits\AiProductTrait;


class FillProductContents extends BaseAi
{
    use AiProductTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openai:fill-product-contents {ai?} {model?} {temperature?} {test_limits?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update catalog cache';

    const TEST_LIMITS = 1;
    const REQUEST_ATTEMPTS = 2;

    const DEFAULT_AI = 'gpt';
    const DEFAULT_MODEL = 'gpt-4.1';
    const DEFAULT_TEMP = 0.5;
    

    private $ai = null;
    private $model = null;
    private $temperature = null;
    private $test_limits = null;

    private $lang = 'uk';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->ai = $this->argument('ai') ?? self::DEFAULT_AI;
        $this->model = $this->argument('model') ?? self::DEFAULT_MODEL;
        $this->temperature = $this->argument('temperature') ?? self::DEFAULT_TEMP;

        $this->test_limits = $this->argument('test_limits') ?? self::TEST_LIMITS;

        $this->loadSettings();


        // Fill content
        $prompts = Prompt::where('is_active', 1)->get();
        foreach($prompts as $prompt) {
            if($prompt->categories) {
                foreach($prompt->categories as $category) {
                    $this->fillContent($prompt->content, $category->nodeIds);
                }
            }
        }
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
     * Method fillContent
     *
     * @param $prompt $prompt [explicite description]
     * @param $categoryIds $categoryIds [explicite description]
     *
     * @return void
     */
    private function fillContent($prompt, $categoryIds) {
        // Start with filtered query based on settings
        $products = $this->getFilteredProductsQuery();
        
        // Add content-specific filters
        $products = $products->where(function($query) {
            foreach($this->langs_list as $lang_key) {
                $query->whereRaw('LENGTH(JSON_EXTRACT(content, "$.' . $lang_key . '")) < ? ', 150);
            }
            $query->orWhere('content', null);
        })->whereHas('categories', function($query) use($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        })->has('brand');

        $products_count = $products->count();
        $products_cursor = $products->cursor();

        $bar = $this->output->createProgressBar($products_count);
        $bar->start();

        foreach($products_cursor as $index => $product) {

            if($this->test_limits && $index >= $this->test_limits) {
              break;
            }

            $gh = AiGenerationHistory::createItem($product, ['field' => 'content', 'ai' => $this->ai, 'model' => $this->model, 'temperature' => $this->temperature]);
            
            try {
                $content = $this->getProductContent($product, $prompt);

                if(!$content) {
                    $gh->updateStatus('error', 'No content in response.');
                    continue;
                }

                $product->setTranslation('content', 'uk', $content);
                $product->is_ai_content = 1;
                $product->save();

                $gh->updateStatus('done', '');
                $this->info('Product id = ' . $product->id . ', slug = ' . $product->slug  . ' processed');
            } catch(\Exception $e) {
                $gh->updateStatus('error', $e->getMessage());
                $this->error($e->getMessage());
                continue;
            }

            $bar->advance();
        }

        $bar->finish();
    }

    /**
     * openaiCall
     *
     * @return void
     */
    private function getProductContent($product = null, $prompt = null) {

        $prompt_additions = $this->loadPromptFromFile('content-additions.txt');
        $final_prompt = $prompt . "\n\n" . $prompt_additions;

        $user_data = [
            "name" => $product->name,
            "brand" => $product->brand->name,
            'lang' => $this->lang
        ];

        $user_data_json = json_encode($user_data, JSON_UNESCAPED_UNICODE);


        $result = $this->client->chat()->create([
            'temperature' => $this->temperature,
            'model' => $this->model,
            'messages' => [
            [
                'role' => 'system',
                'content' => $prompt
            ],
            [
                'role' => 'user',
                'content' => $user_data_json
            ],
            ],
        ]);

        return $result->choices[0]->message->content ?? null;
    }
}
