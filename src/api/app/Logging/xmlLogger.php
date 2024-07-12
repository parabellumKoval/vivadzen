<?php
 
namespace App\Logging;

use Illuminate\Support\Facades\Log;
 
class xmlLogger
{

  private $name = 'xml';

    /**
     * Create a new xmlLogger instance.
     *
     * @return void
     */
    public function __construct($name)
    {
      $this->name = $name;
    }
  
  /**
   * print
   *
   * @param  mixed $message
   * @return void
   */
  private function print($message) {
    Log::build([
      'driver' => 'daily',
      'path' => storage_path('logs/xml/' . $this->name . '/' . $this->name . '.log'),
      'days' => 7,
    ])->info($message);
  }
  
  /**
   * printStart
   *
   * @return void
   */
  public function printStart() {
    $info = "\n________________________________________________ \n";
    $info .= "START LOADIND DATA: " . date("Y-m-d H:i:s");
    $info .= "\n________________________________________________ \n";
    $this->print($info);
  }
  
  /**
   * printTotal
   *
   * @param  mixed $total
   * @param  mixed $new
   * @param  mixed $updated
   * @return void
   */
  public function printTotal($total, $new, $updated) {
    $info = "\n________________________________________________ \n";
    $info .= "SUMMARY:\n";
    $info .= "loading data from source completed: " . date("Y-m-d H:i:s") . "\n";
    $info .= "TOTAL RECORDS = " . $total . "\n";
    $info .= "NEW RECORDS = " . $new . "\n";
    $info .= "UPDATED RECORDS = " . $updated;
    $info .= "\n________________________________________________ \n";
    $this->print($info);
  }
  
  /**
   * printItem
   *
   * @param  mixed $product
   * @param  mixed $old_product
   * @param  mixed $index
   * @return void
   */
  public function printItem($product, $old_product = null, $index) {
    $info = $index + 1 . ') ';

    if(!empty($old_product)) {
      $info .= 'ID = ' . $old_product->id;
    }else {
      $info .= 'NEW';
    }

    $info .= ', CODE = ' . $product->code . ', NAME = ' . $product->name;

    if(!empty($old_product)) {
      if($old_product->price != $product->price) {
        $info .= ', PRICE ' . $old_product->price . ' >>> ' . $product->price;
      }

      if($old_product->in_stock != $product->in_stock) {
        $info .= ', AMOUNT ' . $old_product->in_stock . ' >>> ' . $product->in_stock;
      }
    }

    $info .= "\n";
    
    $this->print($info);
  }
}