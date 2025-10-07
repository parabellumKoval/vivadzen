<?php
	
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use \Backpack\Store\app\Models\Product;

class FavoritesController extends Controller
{
  protected $product_small_resource_class;

  function __construct() {
    $this->product_small_resource_class = config('backpack.store.resource.small', 'App\Http\Resources\ProductSmallResource');
  }

  public function index(Request $request){

    $data = $request->only(['user_id']);

    if(!isset($data['user_id']) || empty($data['user_id'])){
      throw new \Exception('User id not presented');
    }

    $products = Product::query()
      ->select(\DB::raw('ak_products.*, MAX(fv.id)'))
      ->distinct('ak_products.id')
      ->base()
      ->active()
      ->leftJoin('favorites as fv', 'fv.product_id', '=', 'ak_products.id')
      ->where('fv.user_id', $data['user_id'])
      ->groupBy('ak_products.id');

    $per_page = request('per_page', config('backpack.store.order.per_page', 12));
    $products = $products->paginate($per_page);
    $products = $this->product_small_resource_class::collection($products);

    return $products;
  }

  public function ids(Request $request){

    $data = $request->only(['user_id']);

    if(!isset($data['user_id']) || empty($data['user_id'])){
      throw new \Exception('User id not presented');
    }

    $ids = \DB::table('favorites')->select('favorites.*')->where('user_id', $data['user_id'])->get();
   
    if(!$ids){
      return [];
    }
    
    return $ids->pluck('product_id');
  }

  public function sync(Request $request){
    $data = $request->only(['user_id', 'product_id']);

    if(!isset($data['user_id']) || empty($data['user_id'])){
      throw new \Exception('User id is not presented');
    }

    if(!isset($data['product_id']) || !$data['product_id']){
      throw new \Exception('Product id is not presented');
    }

    $favorite = \DB::table('favorites')->select('favorites.*')
                ->where('user_id', $data['user_id'])
                ->where('product_id', $data['product_id'])
                ->first();

    if(!$favorite) {
      try {
        \DB::table('favorites')->insert([
          'user_id' => $data['user_id'],
          'product_id' => $data['product_id']
        ]);
      }catch(\Exception $e) {
        throw $e; 
      }

      return [
        'type' => 'add',
        'id' => intval($data['product_id'])
      ];
    }else {
      try {
        \DB::table('favorites')->select('favorites.*')
          ->where('user_id', $data['user_id'])
          ->where('product_id', $data['product_id'])
          ->delete();
      }catch(\Exception $e) {
        throw $e; 
      }

      return [
        'type' => 'remove',
        'id' => intval($data['product_id'])
      ];
    }
    
    return true;
  }
}