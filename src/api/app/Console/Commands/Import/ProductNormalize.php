<?php

namespace App\Console\Commands\Import;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Facades\App;


use Illuminate\Support\Facades\Storage;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use App\Services\ImageUploader\Facades\ImageUploader;

use App\Models\Category;
use App\Models\StoreProduct;

class ProductNormalize extends Command
{
    protected $signature = 'import:products-normalize';
    protected $description = 'Normalize products after import';


    private $totalRecords = 0;


    public function handle()
    {
        $this->normalizeAll();
    }

    public function normalizeAll() {
        $products = StoreProduct::cursor();
        $languages_array = config('backpack.crud.locales', []);
        $languages = array_keys($languages_array);

        foreach($products as $product) {
            $translations = [];
            
            foreach($languages as $lang) {
                // Process name
                $name = $product->getTranslation('name', $lang, false);
                if($name) {
                    // Remove HTML
                    $name = strip_tags($name);
                    // Remove double quotes
                    $name = str_replace('"', '', $name);
                    $translations['name'][$lang] = $name;
                }

                // Process short_name
                $shortName = $product->getTranslation('short_name', $lang, false);
                if($shortName) {
                    // Remove quotes from start and end
                    $shortName = trim($shortName, '"');
                    $translations['short_name'][$lang] = $shortName;
                }
            }

            // Update translations
            foreach($translations as $field => $values) {
                $product->setTranslations($field, $values);
            }

            $product->save();
        }

        $this->info('Products have been normalized successfully.');
    }
}
