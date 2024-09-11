<?php
namespace App\Traits;

trait ProductProcessing {
  /**
   * mergeSupplierProductsTrait
   *
   * @param  mixed $sp
   * @param  mixed $duplicate_sps
   * @return void
   */
  private function mergeSupplierProductsTrait($all_sps) {
    $sorted = $all_sps->sortByDesc(function ($sp, $key) {
      $filled_fileds = 0;

      $filled_fileds += !empty($sp->price)? 1: 0;
      $filled_fileds += !empty($sp->old_price)? 1: 0;
      $filled_fileds += !empty($sp->in_stock)? 1: 0;
      $filled_fileds += !empty($sp->code)? 1: 0;
      $filled_fileds += !empty($sp->barcode)? 1: 0;
      $filled_fileds += !empty($sp->product->name)? 1: 0;
      $filled_fileds += !empty($sp->product->content)? 1: 0;
      $filled_fileds += !empty($sp->product->images)? 1: 0;
      $filled_fileds += !empty($sp->product->brand_id)? 1: 0;
      $filled_fileds += !empty($sp->product->is_active)? 10: 0;
      $filled_fileds += !empty($sp->product->is_trans)? 5: 0;
      $filled_fileds += !empty($sp->product->is_bunny)? 3: 0;


      return $filled_fileds;
    });
    
    $main_sp = $sorted->splice(0, 1)->first();
    $other_sps = $sorted;

    foreach($other_sps as $sp) {
      // We mark the product as a duplicate of another, so that we can then combine the SupplierProduct duplicates together 
      // and delete the duplicate product cards
      $sp->product->duplicate_of = $main_sp->product_id;
      $sp->product->save();

      // if($sp->supplier_id !== $main_sp->supplier_id){
      //   $this->info('Different suppliers');
      //   // change relative 
      //   $sp->product_id = $main_sp->product_id;
      //   $sp->save();
      // }else {
      //   $this->info('Delete dublicated from one supplier');
      //   $sp->delete();
      // }
    }
  }
}