<?php

namespace App\Console\Commands\Catalog\Xml;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use League\Csv\Reader;

use App\Traits\ProductProcessing;

use App\Models\Category;
use App\Models\Product;
use App\Models\CategoryFeed;
use App\Models\Feed;

class PromCategories extends Command
{
    use ProductProcessing;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xml:prom_categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'We take the prepared xml file with data about duplicates and combine the products';

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
     * @return mixed
     */
    public function handle()
    {
      // Part 1
			// $xml = $this->getXML();

      // Part 2
      // $this->fillCategories();

      // Part 3
      CategoryFeed::truncate();
      $this->fillParentId();

      //
      // $this->fix0Parent();

      // Restore base category parent id
      // $this->restoreParentId();
    }
              
    /**
     * restoreParentId
     *
     * @return void
     */
    private function restoreParentId(){
      $old_base_ids = [
        // БАДы
        [
          'old' => 72879515,
          'new' => 136052335
        ],
        // Продукты
        [
          'old' => 72879517,
          'new' => 136054110
        ],
        // Косметика
        [
          'old' => 72879518,
          'new' => 136052440
        ],
        // Товары для дома
        [
          'old' => 72879523,
          'new' => 136061701
        ],
        // Спортивное питание 
        [
          'old' => 113578441,
          'new' => 136052686
        ],
        // АППЛИКАТОР КУЗНЕЦОВА
        [
          'old' => 113404584,
          'new' => 136053821
        ],
        // ТЕРМОБЕЛЬЕ
        // [
        //   'old' => 0,
        //   'new' => 136054312
        // ]
      ];

      foreach($old_base_ids as $ids){
        $cf = CategoryFeed::where('id', $ids['new'])->first();
        
        $cfs = CategoryFeed::where('prom_parent_id', $ids['new'])->get();
      }

    }
    
    /**
     * fix0Parent
     *
     * @return void
     */
    private function fix0Parent() {
      $cfs = CategoryFeed::where('prom_parent_id', 0)->get();

      foreach($cfs as $cf) {
        $cf->update(['prom_parent_id' => null]);
      }
    }

    /**
     * fillParentId
     *
     * @return void
     */
    private function fillParentId() {
      $content = file_get_contents(url('/uploads/prom-cats.csv'));

      $reader = Reader::createFromString($content);
      $reader->setDelimiter(';');
      $records = $reader->getRecords();

      // Get prom feed
      $feed = Feed::where('key', 'prom')->first();

      foreach($records as $index => $record) {
        if($index === 0) {
          continue;
        }

        $category_feed = CategoryFeed::where('prom_id', (int)$record[0])->first();

        if(!$category_feed) {
          $this->line('Category no exists ' . $record[0] . ' - ' . $record[1]);
          $category_feed = $this->createCategoryFeed($record, $feed->id);
          // $this->line('skip category no exists ' . $record[0] . ' - ' . $record[1]);
          // continue;
        }

        $this->info('index ' . $index);

        $category_feed->update([
          'prom_parent_id' => (int)$record[4]
        ]);
      }    
    }
        
    /**
     * createCategoryFeed
     *
     * @param  mixed $record
     * @param  mixed $feed_id
     * @return void
     */
    private function createCategoryFeed($record, $feed_id) {

      $this->line('create category');

      $cat = Category::
        where('name->ru', $record[1])
        ->orWhere('name->uk', $record[1])
        ->first();

      $category_feed = CategoryFeed::create([
        'category_id' => $cat->id ?? null,
        'feed_id' => $feed_id,
        'prom_name' => $record[1],
        'prom_id' => (int)$record[0],
        'prom_parent_id' => (int)$record[4],
      ]);

      return $category_feed;
    }

    /**
     * fillCategories
     *
     * @return void
     */
    private function fillCategories() {
      $content = file_get_contents(url('/uploads/prom_categories_2.csv'));

      $reader = Reader::createFromString($content);
      $reader->setDelimiter(';');
      $records = $reader->getRecords();

      // Get prom feed
      $feed = Feed::where('key', 'prom')->first();

      foreach($records as $index => $record) {
        if($index === 0) {
          continue;
        }

        if(CategoryFeed::where('prom_id', (int)$record[0])->first()) {
          $this->line('skip category allready isset ' . $record[1]);
          continue;
        }

        $this->info('index ' . $index);
        
        $cat = Category::
                          where('name->ru', $record[1])
                          ->orWhere('name->uk', $record[1])
                          ->first();

        if($cat) {
          $this->info('Find site category ' . $cat->name);
        }

        CategoryFeed::create([
          'category_id' => $cat->id ?? null,
          'feed_id' => $feed->id,
          'prom_name' => $record[1],
          'prom_id' => (int)$record[0],
        ]);
      }      
    }
        
    /**
     * getXML
     *
     * @return void
     */
    private function getXML() {
      $content = file_get_contents(url('/uploads/prom_categories.csv'));

      $reader = Reader::createFromString($content);
      $reader->setDelimiter(';');
      $records = $reader->getRecords();

      // Get prom feed
      $feed = Feed::where('key', 'prom')->first();

      foreach($records as $index => $record) {
        if($index === 0) {
          continue;
        }

        $this->info('index ' . $index);
        
        $cat = Category::where('name->ru', $record[1])->first();

        if($cat) {
          $this->info('Find site category ' . $cat->name);
        }

        CategoryFeed::create([
          'category_id' => $cat->id ?? null,
          'feed_id' => $feed->id,
          'prom_name' => $record[1],
          'prom_id' => (int)$record[0],
        ]);
      }
    }
}
