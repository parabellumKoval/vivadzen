<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;

class ReplaceHtmlSymbolsInProducts extends Command
{
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:replace-html-symbols-in-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace htmlspecialchars_decode symbols in products';
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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $this->replaceSymbols();
      // $this->TestReplaceSymbols();
      return 0;
    }
    
    private function replaceSymbolsInProduct($product) {
      $ru_name = $product->getTranslation('name', 'ru');
      $ru_name = !empty($ru_name) ? htmlspecialchars_decode($ru_name) : $ru_name;
      $product->setTranslation('name', 'ru', $ru_name);

      $uk_name = $product->getTranslation('name', 'uk');
      $uk_name = !empty($uk_name) ? htmlspecialchars_decode($uk_name) : $uk_name;
      $product->setTranslation('name', 'uk', $uk_name);

      $ru_content = $product->getTranslation('content', 'ru');
      $ru_content = !empty($ru_content) ? htmlspecialchars_decode($ru_content) : $ru_content;
      $product->setTranslation('content', 'ru', $ru_content);

      $uk_content = $product->getTranslation('content', 'uk');
      $uk_content = !empty($uk_content) ? htmlspecialchars_decode($uk_content) : $uk_content;
      $product->setTranslation('content', 'uk', $uk_content);

      $product->save();
    }
    
    /**
     * replaceSymbols
     *
     * @return void
     */
    private function replaceSymbols() {
      $products = Product::where('id', '>', 0);
      $products_cursor = $products->cursor();
      $products_count = $products->count();
      
      $bar = $this->output->createProgressBar($products_count);
      $bar->start();

      foreach($products_cursor as $product) {
        $this->replaceSymbolsInProduct($product);
        $bar->advance();
      }

      $bar->finish();
    }

    
}