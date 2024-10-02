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

      $acs = \DB::table('ak_attribute_category')->select('*')->get();

      $bar = $this->output->createProgressBar(count($acs));
      $bar->start();

      foreach($acs as $ac) {
        if(isset($processed[$ac->attribute_id][$ac->category_id])) {
          $this->info('Duplication ' . $ac->attribute_id . ' - ' . $ac->category_id);
          \DB::table('ak_attribute_category')->where('id', $ac->id)->delete();
          continue;
        }

        $processed[$ac->attribute_id][] = $ac->category_id;

        //
        $attribute = Attribute::find($ac->attribute_id);

        if(!$attribute) {
          $this->error('Attribute ' . $ac->attribute_id . ' was not found. Delete recond.');
          \DB::table('ak_attribute_category')->where('id', $ac->id)->delete();
          continue;
        }

        //
        $category = Category::find($ac->category_id);

        if(!$category) {
          $this->error('Category ' . $ac->category_id . ' was not found. Delete category.');
          \DB::table('ak_attribute_category')->where('id', $ac->id)->delete();
          continue;
        }

        $bar->advance();
      }

      $bar->finish();
    }
}
