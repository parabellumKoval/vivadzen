<?php

namespace App\Console\Commands\Catalog\Xml;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Models\Product;


ini_set('memory_limit', '500M');


class BelokUaUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:xml:belokua';

    protected $logger = null;
		protected $url = null;
		protected $brandsList = null;
    protected $totalRecords = 0;
    protected $totalNew = 0;
    protected $totalUpdated = 0;
		
		
// 		private const BASE_OVERPRICE = 1.4;
// 		private const ALT_OVERPRICE = 1.4;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update products from belok.ua pricelist (XML)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->url = config('console.xml.belok.link');
        $this->brandsList = config('console.xml.belok.brands');
        $this->logger = new \App\Logging\xmlLogger('belok');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	    
        $old_product = null;

				$xml = $this->getXMLCatalog();
				
        $this->totalRecords = count($xml->item);
        
        $bar = $this->output->createProgressBar($this->totalRecords);
				$bar->start();

        $this->logger->printStart();
       
        for($i = 0; $i < $this->totalRecords; $i++){
	        $is_new_product = true;
	        
        	$xml_product = [
		        'title' => $xml->item[$i]->title->__toString(),
		        'brand' => $xml->item[$i]->brand->__toString(),
	        	'count' => $xml->item[$i]->qty->__toString(),
		        'articul' => $xml->item[$i]->art->__toString(),
	        	'price' => $xml->item[$i]->rrc->__toString()
        	];
        	
        	
        	// LEAVE ONLY PRODUCTS FROM ALLOWED BRANDS LIST
        	if(!in_array(trim($xml_product['brand']), $this->brandsList, true))
        		continue;
        	
        	// TRY TO FIND EXISTE PRODUCT
        	$product = Product::where('code', $xml_product['articul'])->first();
        	
        	if(!$product) {
	      		$product = $this->createNewProduct($xml_product);
            $this->totalNew += 1;
	      		
	      		if(!$product) {
	      			continue;
	      		}
        	}
        	else {
            $old_product = clone $product;

            if($old_product->price !== (float)$xml_product['price'] || $old_product->in_stock !== $xml_product['count']) {
              $this->totalUpdated += 1;
            }

	        	$is_new_product = false;
        	}
        	
        	
					// UPDATE AMOUNT
      		$product->in_stock = $xml_product['count'] === 'N'? 0: 100;
        	
        	if($product->in_stock) {
        		// UPDATE PRICE
						$product->price = (float)$xml_product['price'];
					}
					
          $this->logger->printItem($product, $old_product, $i);

      		$product->save();
      		
        	if($is_new_product) {
	        	// SET DEFAULT CATEGORY
	        	$product->categories()->attach(config('console.xml.default_category_id'));
	        	
	      		// UPDATE BRAND OF NEW PRODUCT
	      		// $product = $this->updateProductBrand($product);
        	}
      		
      		$bar->advance();	
        }
        
				$bar->finish();

        $this->logger->printTotal($this->totalRecords, $this->totalNew, $this->totalUpdated);
    }
    
    private function createNewProduct($xml_product) {
  		$product = new Product();
  		
			$product->is_active = 0;

			// SET AMOUNT
			$product->in_stock = $xml_product['count'] === 'N'? 0: 100;
			
  		// UPDATE PRICE
			$product->price = (float)$xml_product['price'];
			
			// If this is new product and not available on has not price - don't save it in db
			if(!$product->in_stock || !$product->price)
				return null;
  	
			// SET TITLE TO NEW PRODUCT
  		// $product->name = mb_strimwidth($xml_product['title'], 0, 191);
      $product->setTranslation('name', 'ru', $xml_product['title']);
  		
  		// SET SLUG TO NEW PRODUCT
  		// $product->slug = mb_strimwidth(str_slug($xml_product['title']), 0, 189) . '-' . rand(0,9);
  		
  		// SET ARTICUL TO NEW PRODUCT
  		$product->code = $xml_product['articul'];
  		
  		// SET SOURCE TO NEW PRODUCT
  		$product->parsed_from = 'belok.ua';
  		
  		// SET SUPPLIER TO NEW PRODUCT
  		$product->supplier_id = 42;
  		
  		// SET HELPERS
  		// $product->content = 'Бренд: ' . $xml_product['brand'];
  		
  		return $product;
    }

    
    private function getXMLCatalog() {
	    try 
	    {
	    	$xml = simplexml_load_file($this->url);
	    }
	    catch(\Exception $e)
	    {  
		    $message = "Can't get products catalog BELOK: " . $e->getMessage();
		    
				Log::channel('xml')->error($message);
				throw new \Exception($message);
			}
	    
	    return $xml->Catalog->items;
    }
    
    
    // private function updateProductBrand($product) {
    // 	// GET BRAND PROPERTY FROM THE ROOT CATEGORY
    // 	$root_category = $product->category()->first()->getRootCategory();
  	// 	$brand_property = $root_category->properties->where('name', 'Бренд')->first();
  		
  	// 	if(!$brand_property)
		// 		return;
    	
    // 	preg_match('/Бренд: (.+)/', $product->intro, $matches);
    // 	$brand = $matches[1] ?? null;
    	
    
    // 	$product->properties()->save($brand_property, ['value' => $brand]);
    	
    //   if (!$root_category->propertiesValues->contains('pivot.value', $brand)) {
    //     $root_category->propertiesValues()->attach($brand_property->id, ['value' => $brand]);
    //   }
      
    //   return $product;   
    // }
    
    // private function updateProductsBrand() {
	    
	  //   $products = Product::where('parsed_from', 'dobavki.ua')->where('created_at', '>=', '2022-12-03')->get();
	    	
    // 	foreach($products as $index => $product){
	  //   	$this->updateProductBrand($product);
    // 	}
    // }
}
