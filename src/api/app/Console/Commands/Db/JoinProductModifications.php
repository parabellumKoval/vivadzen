<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;

class JoinProductModifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:join-product-modifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    private $prosecced_ids = [];
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

      $this->joinModifications();

      return 0;
    }

    public function joinModifications() {
      $products = Product::where('is_active', 1)->where('parent_id', null)->where('short_name', null);
      $products_cursor = $products->cursor();
      $products_count = $products->count();
      
      $bar = $this->output->createProgressBar($products_count);
      $bar->start();

      foreach($products_cursor as $product) {

        $names = explode(',', $product->name);

        if(count($names) <= 1 || in_array($product->id, $this->prosecced_ids)){
          $this->comment('Skip product id = ' . $product->id . ', name = ' . $product->name);
          continue;
        }

        // Set this products as prosecced
        $this->prosecced_ids[] = $product->id;

        $main_name = $names[0];
        $name_parts = array_slice($names, 1);
        $name_parts = array_map(function($item) {
          return trim($item);
        }, $name_parts);

        $choises = [
          ...$name_parts,
          'Skip',
          'Store as special one'
        ];

        // Find all potential modifications for this product
        $maybe_modifications = Product::where('name->ru', 'LIKE', '%' . $names[0] . '%')->where('id', '!=', $product->id)->get();

        // SKIP If not modifications found
        if(!$maybe_modifications->count()) {
          $this->comment("\n" . 'No potentil modifications. Skip product id = ' . $product->id . ', name = ' . $product->name . "\n");
          continue;
        }

        $this->info("\n" . 'For product id = ' . $product->id . ', name = ' . $product->name . ' was found ' . $maybe_modifications->count() . ' potential modifications' . "\n");

        $maybe_modifications_names = [];
        foreach($maybe_modifications as $key => $maybe_modification) {
          // Set this products as prosecced
          $this->prosecced_ids[] = $maybe_modification->id;

          $maybe_modifications_names[] = explode(',', $maybe_modification->name);
          $this->line(($key + 1) . ') id =' . $maybe_modification->id . ', name = ' . $maybe_modification->name . "\n");
        }
        

        $answer = $this->choice(
          'Which of the lines can indicate a modification?',
          $choises,
          0
        );

        if($answer === 'Skip') {
          $this->warn("\n" . 'SKIP' . "\n");
          continue;
        }elseif($answer === 'Store as special one') {
          $this->warn("\n" . 'STORE AS SPECIAL ONE' . "\n");
          continue;
        }

        $answer_index = array_search($answer, $choises);

        $names_array = [];
        foreach($maybe_modifications_names as $maybe_modifications_name) {
          $names_array[] = trim($maybe_modifications_name[$answer_index + 1]);
        }

        $names_string = implode(', ', $names_array);

        if($this->confirm("You choise {$answer} is refer to these values: " . $names_string . '. Is all ok?')) {
          $this->joinProductModifications($product, $maybe_modifications, $answer_index + 1);
        }

        $bar->advance();
      }

      $bar->finish();
    }
    
    /**
     * joinProductModifications
     *
     * @param  mixed $product
     * @param  mixed $modifications
     * @param  mixed $name_index
     * @return void
     */
    private function joinProductModifications($product, $modifications, $name_index) {
      $names = explode(',', $product->name);
      $product->short_name = $names[$name_index];
      $product->save();
      // $this->info("\n" . 'SHORT NAME = ' . $product->short_name . "\n");

      foreach($modifications as $modification) {
        $modification_names = explode(',', $modification->name);
        $modification->short_name = $modification_names[$name_index];
        $modification->parent_id = $product->id;
        $modification->save();
        // $this->info("\n" . 'MODIFICATION SHORT NAME = ' . $modification->short_name . "\n");
      }
    }
}