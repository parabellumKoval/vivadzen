<?php

namespace App\Console\Commands\Catalog\Parse;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Product;
use App\Category;
use App\Property;

use App\Jobs\BelokUa\GetProductLinks;
use App\Jobs\BelokUa\GetProductInfo;

use Goutte\Client;


ini_set('memory_limit', '500M');


class BelokUaParse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'belokua:parse';
    protected $url = 'https://belokua.ua';
		
		protected $brandsList = [
			"AMIX",
			"BlenderBottle",
			"BPI",
			"Fitness authority",
			"Haya Labs",
			"IronMaxx",
			"Olimp Nutrition",
			"Optimum Nutrition",
			"Quamtrax",
			"Redcon1",
			"Sporter"
		];
		
		protected $brand_catalog_links = [
			"/amix/",
			"/blenderbottle/",
			"/bpi-sports/",
			"/fitness-authority/",
			"/haya-labs/",
			"/ironmaxx/",
			"/olimp-sport-nutrition/",
			"/optimum-nutrition/",
			"/quamtrax/",
			"/redcon1/",
			"/sporter/"
		];
		
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse products from belokua.ua';

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
      Log::info('START PARSING');
			
			//$this->getProductLinks();
			$this->parseProductsInfo();
			
    }
    
    
    
    /**
	  *
	  *	1) Go to every product page and parse data
	  *	2) Then save data to product DB table
	  *
	  * @return void
	  *
    **/
    private function parseProductsInfo() {
			//GetProductInfo::dispatch('https://belok.ua/100-whey-protein-2350-gr-banka-francuzskaya-vanil-1/')->onQueue('parse');
			
			foreach($this->brand_catalog_links as $link){

				$file_path = explode("/", $link);
				$file_name = $file_path[1];
				
				$json_isset_data = \Storage::disk('public')->get("belokua/{$file_name}.json");
				$isset_data = json_decode($json_isset_data);
			
	      $bar = $this->output->createProgressBar(count($isset_data));
				$bar->start();
				
				foreach($isset_data as $product_link){
					GetProductInfo::dispatch($product_link)->onQueue('parse');
					$bar->advance();
				}
				
				$bar->finish();
			}
			
    }
    
    
    /**
	  *
	  *	1) Parse each category link and get all products from each page (paginate)
	  *	2) Save each product link to json file by calling function saveDataToFile
	  *
	  * @return void
	  *
    **/
    private function getProductLinks() {
	    
			$client = new Client();
			
      $bar = $this->output->createProgressBar(count($this->brand_catalog_links));
			$bar->start();
			
			foreach($this->brand_catalog_links as $link) {
				GetProductLinks::dispatch($link)->onQueue('parse');
				
      	$bar->advance();
			}
			
			$bar->finish();
    }
    
    
}
