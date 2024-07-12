<?php

namespace App\Console\Commands\Catalog\Prom;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

use App\Product;


class PromProductsUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prom:productsUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update products on PROM.ua';


		private $products = [];
		
		
		private $products_query;
		
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
	    	$this->setProducts();
	    	$this->setProductsQuery();
	    

        $client = new Client(['base_uri' => 'https://my.prom.ua/api/v1/']);
				
				// PROGRESS BAR
				$bar = $this->output->createProgressBar(count($this->products_query));
				$bar->start();
				
				foreach($this->products_query as $product_query) 
				{
	        $response = $client->request('POST', 'products/edit', [
		        'headers' => [
				        'Authorization' => 'Bearer 16f514b66c20bd0c99f3e1a8923dd7b090915f73'
				    ],
		        'body' => $product_query
	        ]);
					
					$response_content = $response->getBody()->getContents();
					
					Log::info(print_r(json_decode($response_content), true));
      			
      		$bar->advance();
				}
				
				
				$bar->finish();
				
    }
    
    
    
    
    private function setProducts() {
	    $this->products = Product::where('parsed_from', 'proteinplus.pro')->get(['import_id', 'price', 'amount']);
    }
    
    
    private function setProductsQuery() {
	    
	    $products_query = [];
	    
	    for($i = 0; $i < count($this->products); $i++){
		    $products_query[] = [
			    'id' => $this->products[$i]->import_id,
			    'price' => $this->products[$i]->price,
			    'presence' => $this->products[$i]->amount > 0? 'available': "not_available",
			    'quantity_in_stock' => $this->products[$i]->amount
		    ];
	    }
	    
	    $chunked_products_query = array_chunk($products_query, 100);
	    
	    foreach($chunked_products_query as $products_query_slice)
	    {
	    	$this->products_query[] = json_encode($products_query_slice);
	    }
    }
}
