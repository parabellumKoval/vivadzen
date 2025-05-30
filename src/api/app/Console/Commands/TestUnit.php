<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Admin\Product;
use App\Models\Bunny;
use Illuminate\Support\Facades\Http;

class TestUnit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:unit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
      // $this->testBunny();
      $this->testProductSave();
    }


    private function testBunny() {
      $bunny = new Bunny('test');
      $bunny->storeImages([
        [
          'src' => 'https://static1.biotus.ua/media/catalog/product/r/b/rbl-women-ones-new-supplement-facts212.jpg'
        ], [
          'src' => 'https://static1.biotus.ua/media/catalog/product/d/m/dmekupfe.jpeg'
        ]
      ]);
      
    }

    private function testProductSave() {
      $product = Product::find(9999);


      $old_uk_name = $product->getTranslation('name', 'uk', false);
      $old_ru_name = $product->getTranslation('name', 'ru', false);

      $extras_trans_uk = $product->getTranslation('extras_trans', 'uk', false);
      $extras_trans_ru = $product->getTranslation('extras_trans', 'ru', false);

      $new_extras_trans_uk = array_merge($extras_trans_uk, ['old_name' => $old_uk_name]);
      $new_extras_trans_ru = array_merge($extras_trans_ru, ['old_name' => $old_ru_name]);
      
      dd($new_extras_trans_ru, $new_extras_trans_uk);
      
      $product->setTranslation('extras_trans', 'ru', $new_extras_trans_ru);
      $product->setTranslation('extras_trans', 'uk', $new_extras_trans_uk);

      $product->save();

      // dd($product);
    }

    private function getRemote() {
      // $product = Product::find(9999);
      // dd($product->shouldBeSearchable());
      // $url = "https://static1.biotus.ua/media/catalog/product/r/b/rbl-women-ones-new-supplement-facts212.jpg";
      // $url = "https://static1.biotus.ua/media/catalog/product/d/m/dmekupfe.jpeg";

    //   $headers = [
    //     ':authority' => 'static1.biotus.ua',
    //     ':method' => 'GET',
    //     ':path' => '/media/catalog/product/r/b/rbl-women-ones-new-supplement-facts212.jpg',
    //     ':scheme' => 'https',
    //     'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
    //     'accept-encoding' => 'gzip, deflate, br, zstd',
    //     'accept-language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
    //     'cache-control' => 'max-age=0',
    //     'dnt' => '1',
    //     'if-modified-since' => 'Tue, 18 Mar 2025 10:26:47 GMT',
    //     'if-none-match' => '"b45b0ba5e7b649d84d0fe9ac914d2915"',
    //     'priority' => 'u=0, i',
    //     'sec-ch-ua' => '"Google Chrome";v="135", "Not-A.Brand";v="8", "Chromium";v="135"',
    //     'sec-ch-ua-mobile' => '?0',
    //     'sec-ch-ua-platform' => '"macOS"',
    //     'sec-fetch-dest' => 'document',
    //     'sec-fetch-mode' => 'navigate',
    //     'sec-fetch-site' => 'none',
    //     'sec-fetch-user' => '?1',
    //     'upgrade-insecure-requests' => '1',
    //     'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
    // ];

    // $headers = [
    //   'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
    //   'accept-encoding' => 'gzip, deflate, br, zstd',
    //   'accept-language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
    //   'cache-control' => 'max-age=0',
    //   'dnt' => '1',
    //   'if-modified-since' => 'Tue, 18 Mar 2025 10:26:47 GMT',
    //   'if-none-match' => '"b45b0ba5e7b649d84d0fe9ac914d2915"',
    //   'priority' => 'u=0, i',
    //   'sec-ch-ua' => '"Google Chrome";v="135", "Not-A.Brand";v="8", "Chromium";v="135"',
    //   'sec-ch-ua-mobile' => '?0',
    //   'sec-ch-ua-platform' => '"macOS"',
    //   'sec-fetch-dest' => 'document',
    //   'sec-fetch-mode' => 'navigate',
    //   'sec-fetch-site' => 'none',
    //   'sec-fetch-user' => '?1',
    //   'upgrade-insecure-requests' => '1',
    //   'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36',
    //   'referer' => 'https://biotus.ua/',
    // ];

      // $headers = [
      //   'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
      //   'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
      //   'Accept-Language' => 'en-US,en;q=0.9',
      //   'Accept-Encoding' => 'gzip, deflate, br',
      //   'Connection' => 'keep-alive',
      //   'Referer' => 'https://djini.com.ua/',
      //   'Sec-Fetch-Site' => 'same-origin',
      //   'Sec-Fetch-Mode' => 'navigate',
      //   'Sec-Fetch-User' => '?1',
      //   'Sec-Fetch-Dest' => 'document',
      //   'Upgrade-Insecure-Requests' => '1',
      //   'Sec-Ch-Ua' => '"Chromium";v="123", "Not:A-Brand";v="8"',
      //   'Sec-Ch-Ua-Mobile' => '?0',
      //   'Sec-Ch-Ua-Platform' => '"Windows"'
      // ];

      // $headers = [
      //   'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
      //   'Accept' => 'image/*',
      //   'Referer' => 'https://biotus.ua/'
      // ];

      // $proxy = "http://api.proxiesapi.com/?auth_key=7deca06713f7461e3fede887772f57ac_sr98766_ooPq87&url={$url}";

      // $response = Http::get($proxy);

      // dd($response->body());

    }
}