<?php

namespace App\Console\Commands\Catalog\Xml;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Models\Product;


ini_set('memory_limit', '500M');


class ProteinplusUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:xml:proteinplus';
    protected $url = null;
    protected $logger = null;

    protected $totalRecords = 0;
    protected $totalNew = 0;
    protected $totalUpdated = 0;

		protected $bannedBrandsList = null;

		private const BASE_OVERPRICE = 1.33;
		
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update products from proteinplus pricelist (XML)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->url = config('console.xml.proteinplus.link');
        $this->bannedBrandsList = config('console.xml.proteinplus.banned_brands');

        $this->logger = new \App\Logging\xmlLogger('proteinplus');
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

        $this->totalRecords = count($xml->item);
        
        $bar = $this->output->createProgressBar($this->totalRecords);
				$bar->start();

        $this->logger->printStart();
        
        for($i = 0; $i < $this->totalRecords; $i++){
	        $is_new_product = true;
	        
        	$xml_product = [
		        'name' => $xml->item[$i]->name->__toString(),
		        'brand' => $xml->item[$i]->vendor->__toString(),
	        	'available' => $xml->item[$i]->available->__toString(),
		        'articul' => $xml->item[$i]->vendorCode->__toString(),
	        	'price' => $xml->item[$i]->price->__toString(),
            'count' => 0
        	];

        	// SKIP BANNED BRANDS
        	if(in_array($xml_product['brand'], $this->bannedBrandsList, true))
            continue;

      		if($xml_product['available'] === 'true')
            $xml_product['count'] = 1000;
          else
            $xml_product['count'] = 0;
	        
        	$product = Product::where('code', $xml_product['articul'])->first();
        	
        	if(!$product) {
            $this->totalNew += 1;

        		$product = new Product();
			      $product->is_active = 0;
  		      $product->code = $xml_product['articul'];
        		$product->setTranslation('name', 'ru', $xml_product['name']);
          }
        	else 
          {
            $old_product = clone $product;

            if($old_product->price !== (float)$xml_product['price'] || $old_product->in_stock !== $xml_product['count']) {
              $this->totalUpdated += 1;
            }

	        	$is_new_product = false;
        	}
        		
        	$price = $xml_product['price'] ?? null;
        	
        	if(!$price)
        		continue;
        	     
          // Set Product price
					$product->price = ceil($price * $exchange_rate * self::BASE_OVERPRICE);
      	
          // Set product available
      		$product->in_stock = $xml_product['count'];

          //
      		$product->parsed_from = 'proteinplus.pro';

          // SET SUPPLIER TO NEW PRODUCT
          $product->supplier_id = 28;
      		
          $this->logger->printItem($product, $old_product, $i);

      		$product->save();
        
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
		    $message = "Can't get products catalog PROTEINPLUS: " . $e->getMessage();
		    
				Log::channel('xml')->error($message);
				throw new Exception($message);
			}
	    
	    return $xml->items;
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
					throw new Exception($message);
				}
				
				$exchange_coff = 1.0157;
				$exchange_rates = json_decode($privat_rates);
				
				$usd = array_filter($exchange_rates, function($item) {
					return $item->ccy === "USD";
				});
				
				$usd = reset($usd);
				
				
				return (float)$usd->sale * $exchange_coff;	    
    }
}
