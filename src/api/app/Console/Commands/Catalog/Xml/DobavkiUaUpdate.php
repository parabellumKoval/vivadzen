<?php

namespace App\Console\Commands\Catalog\Xml;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Models\Product;


ini_set('memory_limit', '500M');


class DobavkiUaUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:xml:dobavkiua';
    protected $url = null;
    protected $logger = null;

    protected $totalRecords = 0;
    protected $totalNew = 0;
    protected $totalUpdated = 0;
	
		protected $bannedBrandsList = null;
		protected $brandsOverprice32 = null;
		protected $brandsOverprice50 = null;
		
		private const BASE_OVERPRICE = 1.3;
		private const ALT_OVERPRICE = 1.5;
		
		private $updated_product_ids = [];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update products from dobavki.ua pricelist (XML)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->url = config('console.xml.dobavkiua.link');

        $this->bannedBrandsList = config('console.xml.dobavkiua.banned_brands');
        $this->brandsOverprice32 = config('console.xml.dobavkiua.brands_overprice_32');
        $this->brandsOverprice50 = config('console.xml.dobavkiua.brands_overprice_50');

        $this->logger = new \App\Logging\xmlLogger('dobavkiua');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $old_product = null;
	    	$exchange_rate = $this->getExchangeRate();
				
				$xml = $this->getXMLCatalog();
        
        $this->totalRecords = count($xml->offer);
        
        $bar = $this->output->createProgressBar($this->totalRecords);
				$bar->start();

        $this->logger->printStart();
        
        for($i = 0; $i < $this->totalRecords; $i++){
	        
	        $is_new_product = true;
	        
        	$xml_product = [
		        'category' => $xml->offer[$i]->category->__toString(),
		        'name' => $xml->offer[$i]->name->__toString(),
		        'brand' => $xml->offer[$i]->brand->__toString(),
	        	'count' => 0,
	        	'available' => $xml->offer[$i]->available->__toString(),
		        'articul' => $xml->offer[$i]->articul->__toString(),
		        'barcode' => $xml->offer[$i]->barcode->__toString(),
	        	'currency' => $xml->offer[$i]->currency->__toString(),
	        	'price' => $xml->offer[$i]->price->__toString(),
        	];

      		$xml_product['count'] = $xml_product['available'] === 'true'? 1000: 0;
        	
        	// SKIP PRODUCTS THAT ARTICUL STARTS WITH sale_
        	if(str_starts_with($xml_product['articul'], 'sale_'))
        		continue;
        	
        	// SKIP BANNED BRANDS
        	if(in_array($xml_product['brand'], $this->bannedBrandsList, true))
        		continue;
        	
        	// TRY TO FIND EXISTE PRODUCT
        	$product = Product::where('code', $xml_product['articul'])->first();
        	
        	if(!$product) {
        		$product = new Product();
        	
			      $product->is_active = 0;

						// SET TITLE TO NEW PRODUCT
        		$product->setTranslation('name', 'ru', $xml_product['name']);
        		
        		// SET SLUG TO NEW PRODUCT
        		// $product->slug = mb_strimwidth(str_slug($xml_product['name']), 0, 189) . '-' . rand(0,9);
        		
        		// SET ARTICUL TO NEW PRODUCT
        		$product->code = $xml_product['articul'];
        		
        		// SET SOURCE TO NEW PRODUCT
	      		$product->parsed_from = 'dobavki.ua';
	      		
	      		// SET SUPPLIER TO NEW PRODUCT
	      		$product->supplier_id = 40;
	      		
	      		// SET HELPERS
	      		// $product->content = 'Категория: ' . $xml_product['category'] . ' - Бренд: ' . $xml_product['brand'];
	      		
	      		// UPDATE BRAND OF NEW PRODUCT
	      		//$product = $this->updateProductBrand($product);

            $this->totalNew += 1;
        	}
        	else 
          {
            $old_product = clone $product;

            if($old_product->price !== (float)$xml_product['price'] || $old_product->in_stock !== $xml_product['count']) {
              $this->totalUpdated += 1;
            }

	        	$is_new_product = false;
        	}
        	
        	
        	// UPDATE PRICE
        	$overPrice = in_array($xml_product['brand'], $this->brandsOverprice50, true)? self::ALT_OVERPRICE : self::BASE_OVERPRICE;
					$product->price = ceil((float)$xml_product['price'] * $exchange_rate * $overPrice);

					// UPDATE AMOUNT
      		$product->in_stock = $xml_product['count'];
      		
          $this->logger->printItem($product, $old_product, $i);
      		
      		$product->save();
      		
      		// Ids that were updated
      		$this->updated_product_ids[] = $product->id;
      		
      		
        	if($is_new_product) {
            // SET DEFAULT CATEGORY
            $default_category_id = config('console.xml.default_category_id', 0);

            if($default_category_id) {
              $product->categories()->attach($default_category_id);
            }
        	}
      		
      			
      		$bar->advance();	
        }
        
				$bar->finish();
				
      $this->logger->printTotal($this->totalRecords, $this->totalNew, $this->totalUpdated);
			
			$this->resetProductsAmount();

    }
        
    /**
     * resetProductsAmount
     *
     * Set in_stock = 0 to products that not presented in xml feed
     * 
     * @return void
     */
    private function resetProductsAmount() {
	    if(count($this->updated_product_ids)) {
	    	$pr = Product::where('parsed_from', 'dobavki.ua')->whereNotIn('id', $this->updated_product_ids)->update(['in_stock' => 0]);
	    }
    }
        
    /**
     * getXMLCatalog
     *
     * @return void
     */
    private function getXMLCatalog() {
	    try 
	    {
	    	$xml = simplexml_load_file($this->url);
	    }
	    catch(\Exception $e)
	    {  
		    $message = "Can't get products catalog DOBAVKI: " . $e->getMessage();
		    
				Log::channel('xml')->error($message);
				throw new \Exception($message);
			}
	    
	    return $xml->offers;
    }
        
    /**
     * getExchangeRate
     *
     * @return void
     */
    private function getExchangeRate() {
	    	try 
	    	{
					$privat_rates = file_get_contents('https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5');
				}
				catch(\Exception $e)
				{
					$message = "Can't get exchange rates: " . $e->getMessage();
					
					Log::channel('xml')->error($message);
					throw new \Exception($message);
				}
				
				$exchange_coff = 1.0157;
				$exchange_rates = json_decode($privat_rates);
				
				$usd = array_filter($exchange_rates, function($item) {
					return $item->ccy === "USD";
				});
				
				$usd = reset($usd);
				
				
				return (float)$usd->sale * $exchange_coff;	    
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
