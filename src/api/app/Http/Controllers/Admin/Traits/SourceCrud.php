<?php

namespace App\Http\Controllers\Admin\Traits;

trait SourceCrud {
  
  // Extends of SetupOperation
  public function setupOperation() {}

  // Extends of ListOperation
  public function listOperation() {}

  // Extends of CreateOperation
  public function createOperation() {}

  /**
   * getExchangeRate
   *
   * @return void
   */
  // private function getExchangeRate() {
  //   try 
  //   {
  //     $privat_rates = file_get_contents('https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5');
  //   }
  //   catch(\Exception $e)
  //   {
  //     $message = "Can't get exchange rates: " . $e->getMessage();
      
  //     Log::channel('xml')->error($message);
  //     throw new \Exception($message);
  //   }
    
  //   $exchange_coff = 1.0157;
  //   $exchange_rates = json_decode($privat_rates);
    
  //   $usd = array_filter($exchange_rates, function($item) {
  //     return $item->ccy === "USD";
  //   });
    
  //   $usd = reset($usd);
    
    
  //   return (float)$usd->sale * $exchange_coff;	    
  // }
}