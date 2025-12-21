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
    $liqpay_data = $request->input('data');
    $liqpay_signature = $request->input('signature');
    $payload = $this->decodeLiqpayPayload($liqpay_data);

    Log::channel('liqpay')->info('LIQPAY RESULT');
    Log::channel('liqpay')->info(print_r($payload, true));

    [$client_url, $has_client_url] = $this->resolveClientUrl();

    $order_code = $this->extractOrderCode($payload, $request);
    $redirect_path = $order_code ? "checkout/complete/{$order_code}" : null;

    if ($payload && $liqpay_data && $liqpay_signature) {
      $liqpay = new LiqPay();

      if($liqpay->getSignatureAttribute($liqpay_data) !== $liqpay_signature) {
        Log::channel('liqpay')->error('Invalid LiqPay signature on results endpoint, skipping order update.');
      } else {
        $this->updateOrder($payload);
      }
    } elseif ($payload && !$liqpay_signature) {
      Log::channel('liqpay')->warning('LiqPay results payload received without signature.');
    } elseif (!$payload) {
      Log::channel('liqpay')->warning('LiqPay results payload is empty or invalid.');
    }

    if (!$redirect_path) {
      Log::channel('liqpay')->warning('Order code was not provided in LiqPay result request. Redirecting to client root.');
    }

    if (!$has_client_url) {
      Log::channel('liqpay')->warning('CLIENT_URL is not configured. Falling back to "/" redirect.');
    }

    $redirect_url = $this->buildRedirectUrl($client_url, $redirect_path);

    return redirect()->to($redirect_url);
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

  private function decodeLiqpayPayload(?string $liqpay_data): ?object {
    if (!$liqpay_data) {
      return null;
    }

    $decoded = base64_decode($liqpay_data, true);

    if ($decoded === false) {
      Log::channel('liqpay')->error('Unable to base64 decode LiqPay payload.');
      return null;
    }

    $payload = json_decode($decoded);

    if (json_last_error() !== JSON_ERROR_NONE) {
      Log::channel('liqpay')->error('Unable to JSON decode LiqPay payload: ' . json_last_error_msg());
      return null;
    }

    return $payload;
  }

  private function extractOrderCode(?object $payload, Request $request): ?string {
    $candidates = [
      $payload->order_id ?? null,
      $request->input('order_id'),
      $request->input('orderId'),
      $request->input('order'),
    ];

    foreach ($candidates as $code) {
      if (is_scalar($code) && $code !== '' && $code !== null) {
        return (string) $code;
      }
    }

    return null;
  }

  private function resolveClientUrl(): array {
    $client_url = config('liqpay.client_url') ?: config('app.client_url') ?: config('app.url');
    $client_url = is_string($client_url) ? trim($client_url) : '';

    if ($client_url === '') {
      return ['/', false];
    }

    $client_url = rtrim($client_url, '/');

    if ($client_url === '') {
      return ['/', false];
    }

    return [$client_url, true];
  }

  private function buildRedirectUrl(string $client_url, ?string $path = null): string {
    if (!$path) {
      return $client_url !== '' ? $client_url : '/';
    }

    $path = ltrim($path, '/');

    if ($client_url === '/' || $client_url === '') {
      return "/{$path}";
    }

    return "{$client_url}/{$path}";
  }

}
