<?php

namespace App\Console\Commands\Catalog\Parse;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Product;
use App\Category;
use App\Property;

use App\Jobs\DobavkiUa\GetProductLinks;
use App\Jobs\DobavkiUa\GetProductLinksAll;
use App\Jobs\DobavkiUa\GetProductInfo;

use Goutte\Client;


ini_set('memory_limit', '500M');


class DobavkiUaParse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dobavkiua:parse';
    protected $url = 'https://dobavki.ua';
		
		protected $brandsList = [
			"21st Century",
			"Natural Factors",
			"Boiron",
			"California Gold Nutrition",
			"Carmex",
			"ChildLife",
			"Country Life",
			"Doctor's Best",
			"Douglas Laboratories",
			"Earth`s Creation",
			"Eucerin",
			"Fairhaven Health",
			"Gerber",
			"Healthy Origins",
			"Irwin Naturals",
			"Jarrow Formulas",
			"KAL",
			"Klaire Labs",
			"Lake Avenue Nutrition",
			"Life Extension",
			"Life-flo",
			"Metagenics", 
			"Mommy's Bliss",
			"NatraBio",
			"Natrol",
			"Naturade",
			"Nature's Plus",
			"Nature's Way",
			"Neocell",
			"Nordic Naturals",
			"Now Foods",
			"NutriBiotic",
			"Nutricology",
			"Orthomol Natal",
			"Osteo Bi-Flex",
			"Pure Encapsulations",
			"Puritan's Pride",
			"Solaray",
			"Solgar",
			"Source Naturals",
			"Swanson",
			"Thorne Research",
			"Vitables",
			"YumEarth",
			"Zand",
		];
		
		protected $brand_catalog_links = [
			"/catalog/filter/brand=859/",
			"/catalog/filter/brand=100/",
			"/catalog/filter/brand=445/",
			"/catalog/filter/brand=312/",
			"/catalog/filter/brand=122/",
			"/catalog/filter/brand=132/",
			"/catalog/filter/brand=1143/",
			"/catalog/filter/brand=267/",
			"/catalog/filter/brand=103/",
			"/catalog/filter/brand=95/",
			"/catalog/filter/brand=1551/",
			"/catalog/filter/brand=838/",
			"/catalog/filter/brand=327/",
			"/catalog/filter/brand=625/",
			"/catalog/filter/brand=159/",
			"/catalog/filter/brand=116/",
			"/catalog/filter/brand=99/",
			"/catalog/filter/brand=446/",
			"/catalog/filter/brand=1555/",
			"/catalog/filter/brand=1595/",
			"/catalog/filter/brand=154/",
			"/catalog/filter/brand=301/",
			"/catalog/filter/brand=1556/",
			"/catalog/filter/brand=982/",
			"/catalog/filter/brand=283/",
			"/catalog/filter/brand=97/",
			"/catalog/filter/brand=157/",
			"/catalog/filter/brand=108/",
			"/catalog/filter/brand=303/",
			"/catalog/filter/brand=105/",
			"/catalog/filter/brand=245/",
			"/catalog/filter/brand=162/",
			"/catalog/filter/brand=163/",
			"/catalog/filter/brand=124/",
			"/catalog/filter/brand=667/",
			"/catalog/filter/brand=1553/",
			"/catalog/filter/brand=1554/",
			"/catalog/filter/brand=101/",
			"/catalog/filter/brand=1475/",
			"/catalog/filter/brand=440/",
			"/catalog/filter/brand=1594/",
			"/catalog/filter/brand=365/",
			"/catalog/filter/brand=164/"
		];
		
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse products from dobavki.ua';

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
// 			$this->parseProductsInfo();
// 				$this->getProductLinksAll();
			$this->parseProductsInfoAll();
			
    }
    
    /**
	  *
	  *	1) Go to every product page and parse data
	  *	2) Then save data to product DB table
	  *
	  * @return void
	  *
    **/
    private function parseProductsInfoAll() {
			
				
			$json_isset_data = \Storage::disk('public')->get("dobavkiua/catalog-4.json");
			$isset_data = json_decode($json_isset_data);
		
      $bar = $this->output->createProgressBar(count($isset_data));
			$bar->start();
			
			foreach($isset_data as $product_link){
				GetProductInfo::dispatch($product_link)->onQueue('parse');
				$bar->advance();
			}
			
			$bar->finish();
		
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
			
			foreach($this->brand_catalog_links as $link){

				$file_path = explode("/", $link);
				$file_name = $file_path[3];
				
				$json_isset_data = \Storage::disk('public')->get("dobavkiua/{$file_name}.json");
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
    private function getProductLinksAll() {
			GetProductLinksAll::dispatch()->onQueue('parse');
// 			GetProductLinksAll::dispatch();
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
    
    /**
	  *
	  *	Parse whole catalog and get links to catalog category pages
	  *
	  * @return void
	  *
    **/
    private function getBrandCatalogLinks() {
	    
			$client = new Client();
			
			$crawler = $client->request('GET', 'https://dobavki.ua/catalog/');
			
	    $brands_list = $this->brandsList;
			$brand_catalog_links = [];
			
			$crawler->filter('.filter-section[data-filter-id=5382] .filter-lv1-i')->each(function ($node) use ($brands_list, &$brand_catalog_links) {
				$name = $node->filter('.filter-title')->text();
				
				if(in_array($name, $brands_list)) {
					$link = $node->filter('a')->attr('data-fake-href');
					print $name. ' ' . $link . "\n";
					$brand_catalog_links[] = $link;
				}
			});
			
			dd($brand_catalog_links);
    }
    
    
}
