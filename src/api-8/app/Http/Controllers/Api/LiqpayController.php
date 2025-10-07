<?php
	
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Models\Liqpay;

use \Backpack\Store\app\Models\Order;

class LiqpayController extends Controller
{
	
	private $server_url = '';
	
	
	// CALLBACK FOR TOP UP BALANCE
	// private function callbackBalance($d){
	// 	$relatedTransaction = Transaction::where('order_code', $d->order_id)->firstOrFail();
	// 	$relatedTransaction->status = $d->status;
	// 	$relatedTransaction->extras = $d;
	// 	$relatedTransaction->save();		
	// }	
	
	// CALLBACK FOR SERVICES
	// private function callbackService($d){
			
	// 	$relatedTransaction = Transaction::where('order_code', $d->order_id)->firstOrFail();
	// 	$relatedTransaction->status = $d->status;
	// 	$relatedTransaction->extras = $d;
	// 	$relatedTransaction->save();
		
	// 	if($d->status != 'success')
	// 		return false;
			
	// 	$this->createReverseTransaction($relatedTransaction);
		
	// 	$this->attachService($relatedTransaction);

	// }	
	
	// CALLBACK FOR PACKAGES
	// private function callbackPackage($d){
	// 	$relatedTransaction = Transaction::where('order_code', $d->order_id)->firstOrFail();
	// 	$relatedTransaction->status = $d->status;
	// 	$relatedTransaction->extras = $d;
	// 	$relatedTransaction->save();
		
	// 	if($d->status != 'success')
	// 		return false;
		
	// 	if(!$relatedTransaction->price->priceable->topUpBalance)
	// 		$this->createReverseTransaction($relatedTransaction);
		
		
	// 	$this->attachPackage($relatedTransaction);
		
	// }	
	
	// private function createReverseTransaction($transaction){
	// 	$newTransaction = $transaction->replicate();
		
	// 	$newTransaction->amount = -1 * $newTransaction->amount;
	// 	$newTransaction->save();
		
	// 	return $newTransaction;
	// }
	
	public function generateForm(Request $request){
		
    $data = $request->only(['amount', 'description', 'action', 'source', 'order']);

    $rules = [
      'amount' => 'required|numeric',
      'order' => 'required'
    ];
    
    $validator = Validator::make($data, $rules);
    
    if ($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }
		
		// MAKE LIQPAY
		$liqpay = new LiqPay();
		$response = $liqpay->formData($data['order'], $data['amount'], $data['description']);
		
		return response()->json($response);
	}

  public function callback(Request $request) {		
    $liqpay_data = $request->data;
    $liqpay_signature = $request->signature;		
    $d = json_decode(base64_decode($liqpay_data));
    
    Log::channel('liqpay')->info('LIQPAY CALLBACK');
    Log::channel('liqpay')->info(print_r($d,true));

		$liqpay = new LiqPay();

    // CHECK ADN COMPARE LIQPAY SIGNATURE WITH LOCAL SIGNATURE 
		if($liqpay->getSignatureAttribute($liqpay_data) !== $liqpay_signature) {
			throw new \Exception('Данные подменены');
    }

    $this->updateOrder($d);
  }

  public function results(Request $request) {
    $liqpay_data = $request->data;
    $liqpay_signature = $request->signature;
    
    $d = json_decode(base64_decode($liqpay_data));
    
    Log::channel('liqpay')->info('LIQPAY RESULT');
    Log::channel('liqpay')->info(print_r($d,true));

    $client_url = config('liqpay.client_url');

    if($this->updateOrder($d)){
      return redirect("{$client_url}/checkout/complete/{$d->order_id}");
    }else {
      return redirect($client_url);
    }
  }


  public function updateOrder($d) {
    if(!$d) {
      Log::channel('liqpay')->error('Order data no isset');
      return false;
    }

    $order = Order::where('code', $d->order_id)->where('price', $d->amount)->first();

    if(!$order) {
      Log::channel('liqpay')->error("Order {$d->order_id} / price {$d->amount} was not found. Status was not update.");
      return false;
    }

    try {
      $order->pay_status = LiqPay::getStatus($d->status);
      $order->save();
    } catch (\Exception $e){
      Log::channel('liqpay')->error($e->getMessage());
      return false;
    }

    return true;
  }

  /*
  |--------------------------------------------------------------------------
  | ROUTE FUNCTIONS
  |--------------------------------------------------------------------------
  */
	
	// public function build_url(array $parts) {
	//     return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') . 
	//         ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') . 
	//         (isset($parts['user']) ? "{$parts['user']}" : '') . 
	//         (isset($parts['pass']) ? ":{$parts['pass']}" : '') . 
	//         (isset($parts['user']) ? '@' : '') . 
	//         (isset($parts['host']) ? "{$parts['host']}" : '') . 
	//         (isset($parts['port']) ? ":{$parts['port']}" : '') . 
	//         (isset($parts['path']) ? "{$parts['path']}" : '') . 
	//         (isset($parts['query']) ? "?{$parts['query']}" : '') . 
	//         (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
	// }

	// public function redirect(Request $request) {
		
	// 	Log::channel('transactions')->info('LIQPAY POST');
		
	// 	$liqpay_data = $request->data;
	// 	$liqpay_signature = $request->signature;		
	// 	$d = json_decode(base64_decode($liqpay_data));
		
	// 	$transaction = Transaction::where('order_code', $d->order_id)->firstOrFail();
		
	// 	$source = $transaction->source;
	// 	$status = $transaction->status;
	// 	$desc = $transaction->description;
		
	// 	// parse source url
	// 	$source_url = parse_url($transaction->source);
		
	// 	// get query array from source url
	// 	if(!empty($source_url['query']))
	// 		parse_str($source_url['query'], $query_arr);
	// 	else
	// 		$query_arr = [];
		
	// 	// add new values to query array
	// 	$query_arr['noty'] = true;
	// 	$query_arr['type'] = 'transaction';
	// 	$query_arr['status'] = $status;
	// 	$query_arr['desc'] = $desc;
		
	// 	// join query array to query string
	// 	$query_string = http_build_query($query_arr);
		
	// 	$source_url['query'] = $query_string;
		
	// 	$redirect_to = $this->build_url($source_url);
		
	// 	return redirect($redirect_to);
	// }
	
	// COMMON FORM DATA ROUTER
		// public function formData(Request $request, $type) {
			
		// 	$this->server_url = config('liqpay.callback_' . $type);
			
		// 	return $this->generateForm($request);
		// }
    
    
	// COMMON CALLBACK ROUTER
		// public function callback(Request $request, $type) {
			
		// 	$liqpay_data = $request->data;
		// 	$liqpay_signature = $request->signature;		
		// 	$d = json_decode(base64_decode($liqpay_data));
			
		// 	Log::channel('transactions')->info('LIQPAY CALLBACK');
		// 	Log::channel('transactions')->info(print_r($d,true));
			
		// 	$liqpay = new Liqpay();
	
		// 	// CHECK ADN COMPARE LIQPAY SIGNATURE WITH LOCAL SIGNATURE
		// 	if($liqpay->getSignatureAttribute($liqpay_data) !== $liqpay_signature)
		// 		throw new \Exception('Данные подменены');
			
		// 	switch ($type) {
		// 		case 'balance':
		// 			$this->callbackBalance($d);
		// 			break;
		// 		case 'service':
		// 			$this->callbackService($d);
		// 			break;
		// 		case 'package':
		// 			$this->callbackPackage($d);
		// 			break;
		// 		default:
		// 			throw new \Exception('Неверный тип запроса');
		// 	}
		// }
    	
	// COMMON CREATE ORDER ROUTER
		// public function createOrder(Request $request, $type){
		// 	$data = $request->data;
		// 	$user = $request->user();
		// 	$price_id = $request->price_id;
		// 	$source = $request->source;
			
		// 	$shop = $request->shop_id? $user->shops()->where('id', $request->shop_id)->firstOrFail(): null;
			
		// 	try{
		// 		$d = json_decode(base64_decode($data));
		// 	}catch(\Exception $e){
		// 		throw new \Exception('Неправитьные данные');
		// 	}
			
		// 	if(!$user)
		// 		throw new \Exception('Отсутсвует пользователь');
			
		// 	switch ($type) {
		// 		case 'balance':
		// 			$this->createTransaction($d->amount, $d->order_id, $user, $shop, null, $d->description, $source);
		// 			break;
		// 		case 'service':
		// 			$this->createTransaction($d->amount, $d->order_id, $user, $shop, $price_id, $d->description, $source);
		// 			break;
		// 		case 'package':
		// 			$this->createTransaction($d->amount, $d->order_id, $user, $shop, $price_id, $d->description, $source);
		// 			break;
		// 		default:
		// 			throw new \Exception('Неверный тип запроса');
		// 	}
		// }
/*
|--------------------------------------------------------------------------
| FUNCTIONS
|--------------------------------------------------------------------------
*/	

		// public function attachPackage($transaction){
			
		// 	// SHOP
		// 	$shop = $transaction->transactionable;
			
		// 	// PACKAGE
		// 	$package = $transaction->price->priceable;
			
		// 	// PRICE ADN AMOUNT
		// 	$price = $transaction->price;
			
		// 	$shop->package_id = $package->id;
			
		// 	if(!$shop->vip_until || $shop->vip_until->lt(\Carbon\Carbon::now())) {
		// 		$shop->vip_until = \Carbon\Carbon::now()->addMonths($price->amount);
		// 	}else {
		// 		$shop->vip_until = $shop->vip_until->addMonths($price->amount);
		// 	}
			
		// 	$shop->save();
			
		// 	$this->giveServices($shop, $package, $price);
		// }
	
		// public function giveServices($shop, $package, $price) {
			
		// 	$mounths = $price->amount;
			
		// 	$services = array(
		// 		'lift' => $package->extras['lift'] * $mounths,
		// 		'vip' => $package->extras['vip'] * $mounths,
		// 		'banner' => $package->extras['banner'] * $mounths,
		// 		'promotion' => $package->extras['promotion'] * $mounths
		// 	);
			
		// 	foreach($services as $name => $amount) {
				
		// 		$service = Service::where('type', $name)->first();
		// 		$shop_service = $shop->services()->where('service_id', $service->id)->first();
				
		// 		if($shop_service)
		// 			$shop->services()->updateExistingPivot($service->id, [
		// 				'amount' => $shop_service->pivot->amount + $amount,
		// 			]);
		// 		else
		// 			$shop->services()->attach($service->id, [
		// 				'amount' => $amount,
		// 			]);	
		// 	}		
		// }
	
		// public function attachService($transaction){
			
		// 	// USER OR SHOP
		// 	$related_model = $transaction->transactionable;
			
		// 	// SERVICE
		// 	$related_service = $transaction->price->priceable;
			
		// 	// PRICE ADN AMOUNT
		// 	$related_price = $transaction->price;
			
		// 	// IF SERVICE ALREADY ATTACHED TO RELATED MODEL
		// 	$existing_relation = $related_model->services()->where('service_id', $related_service->id)->first();
	
		// 	if($existing_relation)
		// 		$related_model->services()->updateExistingPivot($related_service->id, [
		// 			'amount' => $existing_relation->pivot->amount + $related_price->amount,
		// 		]);
		// 	else
		// 		$related_model->services()->attach($related_service->id, [
		// 			'amount' => $related_price->amount,
		// 		]);
		// }
		
		
		// public function createTransaction($amount, $order_code, $user, $shop = null, $price_id = null, $description = null, $source = null, $extras = null){
			
		// 	$transaction = new Transaction([
		// 		'amount' => $amount,
		// 		'order_code' => $order_code,
		// 		'description' => $description,
		// 		'extras' => $extras,
		// 		'price_id' => $price_id,
		// 		'source' => $source
		// 	]);
			
		// 	if($shop)
		// 		$shop->transactions()->save($transaction);
		// 	elseif($user)
		// 		$user->transactions()->save($transaction);
		// 	else
		// 		throw new \Exception('Транзакция не была сохранена по причине отсутствия референта.');
				
		// 	return $transaction;
		// }
		
		// public function result(Request $request){
			
		// 	$liqpay_data = $request->data;
		// 	$liqpay_signature = $request->signature;
			
		// 	$d = json_decode(base64_decode($liqpay_data));
			
		// 	Log::channel('transactions')->info('LIQPAY RESULT');
		// 	Log::channel('transactions')->info(print_r($d,true));
		// }
}