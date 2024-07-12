<?php

namespace App\Console\Commands\Catalog\Prom;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

use App\Product;


class PromProductsImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prom:productsImport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products on PROM.ua';


		private $products = [];
		
		
		private $requestBody = [
			  "url" => "https://djini.com.ua/dobavki_ua_to_prom",
			  "force_update" => false,
			  "only_available" => false,
			  "mark_missing_product_as" => "none",
			  "updated_fields" => [
				  "name",
				  "sku",
			    "price",
			    "images_urls",
			    "presence",
			    "quantity_in_stock",
			    "description",
			    "group"
			  ]
		];
		
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
	    $this->getProductInfo();
	    //$this->import();
			//$this->getStatus();
    }
    
    private function import() {
	    	
      $client = new Client(['base_uri' => 'https://my.prom.ua/api/v1/']);
			
      $response = $client->request('POST', 'products/import_url', [
        'headers' => [
		        'Authorization' => 'Bearer 16f514b66c20bd0c99f3e1a8923dd7b090915f73'
		    ],
        'body' => json_encode($this->requestBody)
      ]);
			
			$response_content = $response->getBody()->getContents();
			
			Log::info(print_r(json_decode($response_content), true));
	    
    }
    
    private function getCategoriesList(){
	    $client = new Client(['base_uri' => 'https://my.prom.ua/api/v1/']);
	    $response = $client->request('GET', 'groups/list', [
		    'headers' => [
		        'Authorization' => 'Bearer 16f514b66c20bd0c99f3e1a8923dd7b090915f73'
		    ],
	    ]);
			
			$response_content = $response->getBody()->getContents();
			
			Log::info(print_r(json_decode($response_content), true));
			
			dd($response_content);
    }
    
    
    private function getProductInfo(){
	    $client = new Client(['base_uri' => 'https://my.prom.ua/api/v1/']);
	    $response = $client->request('GET', 'products/by_external_id/8115', [
		    'headers' => [
		        'Authorization' => 'Bearer 16f514b66c20bd0c99f3e1a8923dd7b090915f73'
		    ],
	    ]);
			
			$response_content = $response->getBody()->getContents();
			
			Log::info(print_r(json_decode($response_content), true));
			
			dd($response_content);
    }
    
    private function getStatus() {
	    $client = new Client(['base_uri' => 'https://my.prom.ua/api/v1/']);
	    $response = $client->request('GET', 'products/import/status/63a6242ea7bca136f701e956', [
		    'headers' => [
		        'Authorization' => 'Bearer 16f514b66c20bd0c99f3e1a8923dd7b090915f73'
		    ],
	    ]);
			
			$response_content = $response->getBody()->getContents();
			
			Log::info(print_r(json_decode($response_content), true));
			
			dd($response_content);
    }
    
}
