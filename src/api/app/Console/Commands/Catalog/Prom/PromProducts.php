<?php

namespace App\Console\Commands\Catalog\Prom;

use Illuminate\Console\Command;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

use App\Product;


class PromProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prom:products-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products on PROM.ua';

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
	    $this->getList();
    }
    
    private function getList() {
	    	
      $client = new Client(['base_uri' => 'https://my.prom.ua/api/v1/']);
			
      $response = $client->request('GET', 'products/list', [
        'headers' => [
		        'Authorization' => 'Bearer 16f514b66c20bd0c99f3e1a8923dd7b090915f73'
		    ]
      ]);
			
			$response_content = $response->getBody()->getContents();
			
			dd($response_content );
	    
    }
    
}
