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

}