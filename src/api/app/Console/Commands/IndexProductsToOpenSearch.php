<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// use Elastic\Elasticsearch\ClientBuilder;
use OpenSearch\ClientBuilder;
use App\Models\Product;

class IndexProductsToOpenSearch extends Command
{
    protected $signature = 'opensearch:index-products';
    protected $description = 'Index products into OpenSearch';

    public function handle()
    {
        $client = ClientBuilder::create()
            ->setHosts(['opensearch:9200']) // имя контейнера или localhost
            ->build();

        $products = Product::take(50)->get();

        foreach ($products as $product) {
            $this->line('Product id = ' . $product->id . ', name = ' . $product->name . ', brand = ' . $product->brand->name);
            // $client->index([
            //     'index' => 'products',
            //     'id'    => $product->id,
            //     'body'  => [
            //         'name' => $product->name,
            //         'brand' => $product->brand->name
            //     ]
            // ]);
        }

        $this->info('Products indexed to OpenSearch.');
    }
}

