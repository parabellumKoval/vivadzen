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
			$xml = $this->getXML();
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
        
        $cat = $site_category = Category::where('name->ru', $record[1])->first();

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
