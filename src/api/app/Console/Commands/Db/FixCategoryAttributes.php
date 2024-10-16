<?php

namespace App\Console\Commands\Db;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Category;
use Backpack\Store\app\Models\Attribute;

class FixCategoryAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:fix-category-attributes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    private $processed = [];
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

        // $this->line('Copy properties');
        $this->fixDb();

        return 0;
    }
        
    /**
     * removeEmpty
     *
     * @return void
     */
    private function fixDb () {

      $acs = \DB::table('ak_attribute_category')->select('*');

      $acs_cursor = $acs->cursor();
      $acs_count = $acs->count();

      $bar = $this->output->createProgressBar($acs_count);
      $bar->start();

      foreach($acs_cursor as $ac) {
        // if this combination of attribute/category already processed
        if(isset($this->processed[$ac->attribute_id]) &&  array_search($ac->category_id, $this->processed[$ac->attribute_id])) {
          $this->error('Duplication. Already processed ' . $ac->attribute_id . ' - ' . $ac->category_id);
          continue;
        }

        // Set as processed
        // This attribute has this category
        $this->processed[(string)$ac->attribute_id][] = (string)$ac->category_id;

        // Try find attribute
        $attribute = Attribute::find($ac->attribute_id);

        // if attributes is not exist
        if(!$attribute) {
          $this->error('Attribute ' . $ac->attribute_id . ' was not found. Delete record.');
          // remove all records with this attribute_id
          \DB::table('ak_attribute_category')->where('attribute_id', $ac->attribute_id)->delete();
          continue;
        }

        // Try find category
        $category = Category::find($ac->category_id);

        // if category is not exist
        if(!$category) {
          $this->error('Category ' . $ac->category_id . ' was not found. Delete category.');
          // remove all records with this category_id
          \DB::table('ak_attribute_category')->where('category_id', $ac->category_id)->delete();
          continue;
        }

        // Duplications
        $duplications = \DB::table('ak_attribute_category')
              ->where('category_id', $ac->category_id)
              ->where('attribute_id', $ac->attribute_id)
              ->where('id', '!=', $ac->id)
              ->delete();

        // if($duplications->count()) {
        //   $duplications->delete();
        // }

        $bar->advance();
      }

      $bar->finish();
    }
}
