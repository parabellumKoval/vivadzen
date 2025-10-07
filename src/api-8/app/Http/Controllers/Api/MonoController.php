<?php
	
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use \MonoPay\Client;
use \MonoPay\Payment;
use \MonoPay\Webhook;

use Backpack\Store\app\Models\Order;
use App\Models\Payment as PaymentHistory;

class MonoController extends Controller
{
	
	private $mono_key = null;
  private $redirect_url = null;
  private $callback_url = null;

  public function __construct() {
    $this->mono_key = config('mono.key');
    //
    $this->redirect_url = config('mono.redirect_url');
    //
    $this->callback_url = config('mono.callback_url');
  }
    
  /**
   * callback route
   *
   * @param  mixed $request
   * @return void
   */
  public function callback(Request $request) {
    $sign = $request->header('X-SIGN');

    try {
      Log::channel('mono')->info('mono callback');

      //створили клієнта - через нього запити будуть слатись
      $monoClient = new Client($this->mono_key);

      //отримання публічного ключа (бажано закешувати)
      $publicKey = $monoClient->getPublicKey();

      //класс для роботи з вебхуком
      $monoWebhook = new Webhook($monoClient, $publicKey, $sign);

      //отримуємо вхідні дані
      $body = $request->getContent();

      //валідуємо дані
      if($monoWebhook->verify($body)) {
          Log::channel('mono')->error('Дані прислав mono');

          $body_array = json_decode($body, true);
          Log::channel('mono')->info(print_r($body_array, true));
          $this->updateOrder($body_array);
      } else {
          Log::channel('mono')->error('Дані прислав шахрай, ігноруємо');
      }
    }catch(\Exception $e) {
      return $e->getMessage();
    }
  }

  /**
   * create route
   *
   * @return void
   */
  public function create(Request $request) {

    $data = $request->only(['code', 'description']);

    $rules = [
      'code' => 'required',
      'description' => 'required'
    ];
    
    $validator = Validator::make($data, $rules);

    if($validator->fails()) {
      return response()->json($validator->errors(), 400);
    }

    $order = Order::where('code', $data['code'])->first();

    if(!$order) {
      return response()->json(['error' => true, 'message' => 'No order', 'status' => 404]);
    }

    try {
      $payment = $this->createPayment($order, $data['description']);
    }catch(\Exception $e) {
      return response()->json(['error' => true, 'message' => $e->getMessage(), 'status' => $e->getCode()]);
    }
    
    return response()->json([
      'status' => 200,
      'url' => $payment['pageUrl']
    ]);

    // return response()->json($payment);
  }
	  
  /**
   * createPayment
   *
   * @return void
   */
  protected function createPayment($order, $description) {

    //
    $monoClient = new Client($this->mono_key);

    //
    $monoPayment = new Payment($monoClient);


    $client_url = config('app.client_url');

    $data = [
      //деталі оплати
      'merchantPaymInfo' => [
          'reference' => $order->code, //номер чека, замовлення, тощо; визначається мерчантом (вами)
          'destination' => $description, //призначення платежу
          'basketOrder' => $this->getCart($order->products),
      ],
      'redirectUrl' => "{$client_url}/checkout/complete/{$order->code}",
      'webHookUrl' => $this->callback_url,
      'validity' => 3600 * 24 * 7, //строк дії в секундах, за замовчуванням рахунок перестає бути дійсним через 24 години
      'paymentType' => 'debit', //debit | hold. Тип операції. Для значення hold термін складає 9 днів. Якщо через 9 днів холд не буде фіналізовано — він скасовується
    ];

    //створення платежу
    $invoice = $monoPayment->create(
      $order->price * 100,
      $data
    );

    return $invoice;
  }
  
  /**
   * getCart
   *
   * @param  mixed $order
   * @return void
   */
  protected function getCart($products) {
    return $products->map(function($product) {
      return [
        'name' => $product->name,
        'qty' => $product->pivot->amount,
        'sum' => $product->price * 100, //сума у мінімальних одиницях валюти за одиницю товару
        'icon' => $product->imageSrc,
        'unit' => 'шт.',
      ];
    });
  }

  
  /**
   * updateOrder
   *
   * @param  mixed $d
   * @return void
   */
  public function updateOrder($d) {
    if(!$d) {
      Log::channel('mono')->error('Order data no isset');
      return false;
    }

    $order = Order::where('code', $d['reference'])->first();

    // Make record in db about transaction
    $this->createPaymentHistoryRecord($d, $order);

    if(!$order) {
      Log::channel('mono')->error("Order {$d['reference']} / price {$d['amount']} was not found. Status was not update.");
      return false;
    }

    try {
      $order->status = self::getStatus($d['status']);
      $order->pay_status = self::getPaymentStatus($d['status']);
      $order->save();
    } catch (\Exception $e){
      Log::channel('mono')->error($e->getMessage());
      return false;
    }

    return true;
  }
  
  /**
   * createPaymentHistoryRecord
   *
   * @param  mixed $data
   * @param  mixed $order
   * @return void
   */
  public function createPaymentHistoryRecord($data, $order) {
    $amount = (int)$data['amount'] === 0? 0: (int)$data['amount'] / 100;

    PaymentHistory::create([
      'order_id' => $order->id ?? null,
      'status' => $data['status'],
      'amount' => $amount,
      'extras' => $data,
      'created_at' => now(),
      'updated_at' => now()
    ]);
  }
  
  
  /**
   * getStatus
   *
   * @param  mixed $mono_status
   * @return void
   */
  public static function getStatus($mono_status) {
    switch($mono_status){
      case 'failure':
          $status = 'failed';
          break;
      default:
          $status = 'new';
    }

    return $status;
  }

  /**
   * getSiteStatus
   *
   * @param  mixed $mono_status
   * @return void
   */
  public static function getPaymentStatus($mono_status) {
    switch($mono_status){
      case 'success':
          $status = 'paied';
          break;
      case 'created':
          $status = 'waiting';
          break;
      case 'processing':
          $status = 'waiting';
          break;
      case 'failure':
          $status = 'failed';
          break;
      default:
          $status = $mono_status;
    }

    return $status;
  }
}