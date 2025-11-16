<?php

namespace App\Console\Commands\Import;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Support\Facades\App;


use Illuminate\Support\Facades\Storage;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

use ParabellumKoval\BackpackImages\Facades\ImageUploader;

use App\Models\Category;
use App\Models\StoreProduct;
use Backpack\Store\app\Models\SupplierProduct;

class ProductNormalize extends Command
{
    protected $signature = 'import:products-normalize';
    protected $description = 'Normalize products after import';


    private $totalRecords = 0;


    public function handle()
    {
        // $this->inheritBrand();
        // $this->clearChildContent();
        $this->inheritCategory();
        // $this->normalizeAll();
    }

    public function inheritBrand() {
        $products = StoreProduct::cursor();
        $counter = 0;

        foreach($products as $product) {
            // Skip products without parent
            if (!$product->parent_id) {
                continue;
            }
            
            // Get parent product
            $parent = $product->parent;
            if (!$parent) {
                continue;
            }

            // Get parent brand ID
            $parentBrandId = $parent->brand_id;
            if (!$parentBrandId) {
                continue;
            }

            // Update brand_id for the child product
            $product->brand_id = $parentBrandId;
            $product->save();
            $counter++;

            if ($counter % 100 === 0) {
                $this->info("Processed {$counter} products");
            }
        }

        $this->info("Brand inheritance completed. Updated {$counter} products.");
    }

    public function inheritCategory() {
        $products = StoreProduct::cursor();
        $counter = 0;

        foreach($products as $product) {
            // Skip products without parent
            if (!$product->parent_id) {
                continue;
            }
            
            // Get parent product
            $parent = $product->parent;
            if (!$parent) {
                continue;
            }

            // Get parent categories
            $parentCategories = $parent->categories->pluck('id')->toArray();
            if (empty($parentCategories)) {
                continue;
            }

            // Sync categories with the child product
            $product->categories()->sync($parentCategories);
            $counter++;

            if ($counter % 100 === 0) {
                $this->info("Processed {$counter} products");
            }
        }

        $this->info("Categories inheritance completed. Updated {$counter} products.");
    }

    public function normalizeAll() {
        // $products = StoreProduct::whereNotNull('content')->cursor();
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

                // Process content
                $content = $product->getTranslation('content', $lang, false);
                if($content) {
                    // First remove all \n characters
                    $content = str_replace('\n', '', $content);

                    // Remove multiple newlines and special characters
                    $content = preg_replace('/\s+/', ' ', $content);
                    
                    // Remove all attributes from HTML tags using regex
                    $content = preg_replace('/<([a-zA-Z0-9]+)\s[^>]*>/', '<$1>', $content);

                    $content = trim($content);
                    $translations['content'][$lang] = $content;
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


    public function clearChildContent()
    {
        $products = StoreProduct::whereNotNull('parent_id')->update(['content' => null, 'excerpt' => null]);

        $this->info("Content clearing completed. Updated products.");
    }

    public function flipPrices() {
        $sps = SupplierProduct::whereNotNull('old_price')->cursor();
        foreach($sps as $sp) {
            $price = $sp->price;
            $oldPrice = $sp->old_price;

            $sp->price = $oldPrice;
            $sp->old_price = $price;
            $sp->save();
        }
    }
}
