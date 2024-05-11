<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Backpack\Store\app\Models\Product;

class NormalizeProductContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db-copy:normalize-product-content';

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
      // $content = "<div id='ff' src='' href='fdf'></div>";
      // $content = '<div id="ff" src=""></div>';
      // $content = '<b id="6ce869ead552-fdsgdfg-234-f23f">';
      // $content = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si",'<$1$2>', $content);
      // $content = preg_replace("/\sid=[\"\'].*[\"\']\s*/si",' ', $content);

      // dd($content);
        $this->normalizeProducts();
        return 0;
    }

    private function normalizeProducts() {

      $page = 0;
      $per_page = 1000;

      do{
        $skip = $page * $per_page;
        $products = Product::whereNotNull('content')->skip($skip)->take($per_page)->get();
        
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach($products as $product) {
          $product->old_content = $product->content;
          $product->save();

          $ru_content = $this->clearContent($product->getTranslation('content', 'ru'));
          $uk_content = $this->clearContent($product->getTranslation('content', 'uk'));

          $product->setTranslation('content', 'ru', $ru_content)->setTranslation('content', 'uk', $uk_content);
          $product->save();

          $bar->advance();
        }

        $bar->finish();

        $page += 1;
      }while($products->count());
      
    }

    private function clearContent($content) {
      
      $dom = new \DOMDocument();
      
      // Normalize ampersands
      $content = preg_replace('/&(?!amp)/', '&amp;', $content);

      // Remove id attributes
      $content = preg_replace("/\sid=[\"\'].*[\"\']\s*/si",' ', $content);

      if(@$dom->loadHTML(mb_convert_encoding("<div>$content</div>", 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {

        $xpath = new \DOMXpath($dom);

        // Clear all headers
        foreach($xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6') as $h) {
          $innerText = $dom->createTextNode($h->textContent);
          $firstChild = $h->childNodes->item(0);
          
          if($firstChild) {
            $h->removeChild($firstChild);
            $h->appendChild($innerText);
          }
        }

        // Remove all spans
        foreach($xpath->query('//span') as $span) {
          while ($span->hasChildNodes()) {
              $child = $span->removeChild($span->firstChild);
              $span->parentNode->insertBefore($child, $span);
          }

          $span->parentNode->removeChild($span);
        }

        // Remove all divs
        foreach($xpath->query('//div/div') as $div) {
          while ($div->hasChildNodes()) {
              $child = $div->removeChild($div->firstChild);
              $div->parentNode->insertBefore($child, $div);
          }

          $div->parentNode->removeChild($div);
        }

        // Remove all empty tags
        foreach($xpath->query('//div/*') as $tag) {
          // dd();

          if(!$tag->hasChildNodes() || empty(trim($tag->textContent))) {
            $tag->parentNode->removeChild($tag);
          }
        }

        $dom->normalizeDocument();

        $html = $dom->saveHTML((new \DOMXPath($dom))->query('/')->item(0));

        $html = preg_replace("/[\r\n]+/", "\n", $html);

        return $html;
      }else {
        return $content;
      }
    }

}
