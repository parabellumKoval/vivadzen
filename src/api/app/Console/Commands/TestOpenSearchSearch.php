<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSearch\ClientBuilder;

class TestOpenSearchSearch extends Command
{
    // Имя и описание команды
    protected $signature = 'test:opensearch-search';
    protected $description = 'Test OpenSearch search functionality: flexible search, similar products, product variations';

    // Внутри команды
    public function handle()
    {
        $client = ClientBuilder::create()
            ->setHosts(['opensearch:9200'])  // Укажи хост
            ->build();

        $this->info('1. Flexible Search (fuzziness, typo handling):');
        $this->flexibleSearch($client);
        
        $this->info('2. Search Similar Products (more_like_this):');
        $this->similarProductsSearch($client);
        
        $this->info('3. Search for Product Variations (by name or base ID):');
        $this->productVariationSearch($client);
    }

    // 1. Гибкий поиск
    protected function flexibleSearch($client)
    {
        $response = $client->search([
            'index' => 'products',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query'     => 'Гель для White Mandarin',
                        'fields'    => ['name^3', 'brand'],
                        'fuzziness' => 'AUTO', // Позволяет находить похожие по опечаткам слова
                    ]
                ]
            ]
        ]);

        $this->info('Result of Flexible Search:');
        $this->line(print_r($response, true));
    }

    // 2. Поиск похожих товаров
    protected function similarProductsSearch($client)
    {
        $response = $client->search([
            'index' => 'products',
            'body' => [
                'query' => [
                    'more_like_this' => [
                        'fields' => ['name', 'brand'],
                        'like' => 'Універсальна антипаразитарна програма для дітей віком 5-7 років, Choice, 8 фітокомплексів',
                        'min_term_freq' => 1,
                        'min_doc_freq' => 1
                    ]
                ]
            ]
        ]);

        $this->info('Result of Similar Products Search:');
        $this->line(print_r($response, true));
    }

    // 3. Поиск модификаций товара
    protected function productVariationSearch($client)
    {
        // Поиск через base_product_id
        $response = $client->search([
            'index' => 'products',
            'body' => [
                'query' => [
                    'term' => [
                        'base_product_id' => 1234 // Поставь свой base_product_id
                    ]
                ]
            ]
        ]);

        $this->info('Result of Product Variation Search (by base_product_id):');
        $this->line(print_r($response, true));

        // Поиск по имени (regexp)
        $response = $client->search([
            'index' => 'products',
            'body' => [
                'query' => [
                    'regexp' => [
                        'name.keyword' => 'Універсальна антипаразитарна програма для дітей віком.*' // Ищет все варианты с этим именем
                    ]
                ]
            ]
        ]);

        $this->info('Result of Product Variation Search (by name):');
        $this->line(print_r($response, true));
    }
}
