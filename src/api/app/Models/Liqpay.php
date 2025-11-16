<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Facades\Http;

class Liqpay extends Model
{	
  use HasFactory;
  
  private $public_key;
  private $private_key;
  
  public $action;
  public $amount;
  public $currency;
  public $description;
  public $order_id;
  public $version;
  public $server_url;
  public $result_url;
  
  public $endpoints = array(
    'checkout' => 'https://www.liqpay.ua/api/3/checkout'
  );

  public const STATUS = [
    'error' => 'failed',
    'failure' => 'failed',
    'success' => 'paied'
  ];

  /*
  |--------------------------------------------------------------------------
  | GLOBAL VARIABLES
  |--------------------------------------------------------------------------
  */
  

  /*
  |--------------------------------------------------------------------------
  | FUNCTIONS
  |--------------------------------------------------------------------------
  */

	public function __construct(){
		$this->version = 3;
		$this->currency = 'UAH';
		$this->action = 'pay';
		$this->public_key = config('liqpay.public_key');
		$this->private_key = config('liqpay.private_key');
		$this->server_url = config('liqpay.callback');
		$this->result_url = config('liqpay.result'); 
	}

	public function formData($order_id, $amount, $description, $server_url = null, $result_url = null, $action = null){
	
		$this->order_id = $order_id;
		$this->amount = $amount;
		$this->description = $description;
		
		if($action)
			$this->action = $action;
			
		if($server_url)	
			$this->server_url = $server_url;
			
		if($result_url)	
			$this->result_url = $result_url;
		
		return array(
			'data' => $this->data,
			'signature' => $this->signature,
			'action' => $this->endpoints['checkout']
		);
	}

	public function pay(){
		
		$data = array(
			'data' => $this->data,
			'signature' => $this->signature
		);
		
		//dd($data);
		$response = Http::asForm()->post('https://www.liqpay.ua/api/3/checkout', $data);
		$results = $response->body();
		
		dd($results);
		echo $results;
	}

  public static function getStatus($liq_status){
    if(!$liq_status)
      return;
    
    return isset(self::STATUS[$liq_status])? self::STATUS[$liq_status]: config('backpack.store.order.pay_status.default');
  }
  /*
  |--------------------------------------------------------------------------
  | RELATIONS
  |--------------------------------------------------------------------------
  */
  
      
  /*
  |--------------------------------------------------------------------------
  | SCOPES
  |--------------------------------------------------------------------------
  */

  /*
  |--------------------------------------------------------------------------
  | ACCESSORS
  |--------------------------------------------------------------------------
  */
	
	public function getDataAttribute(){
		$data = array(
			"public_key" => $this->public_key,
			"version" => $this->version,
			"action" => $this->action,
			"amount" => $this->amount,
			"currency" => $this->currency,
			"description" => $this->description,
			"order_id" => $this->order_id,
		);
		
		if($this->server_url)
			$data['server_url'] = $this->server_url;
		
		if($this->result_url)
			$data['result_url'] = $this->result_url;
		
		$base64_data = base64_encode(json_encode($data));
		
		return $base64_data;
	}

	public function getSignatureAttribute($data = null){
		
		$data = $data? $data: $this->data;
		
		$signature = $this->private_key . $data . $this->private_key;
		
		$base64_signature = base64_encode( sha1( $signature, 1 ) );
		
		return $base64_signature;
	}
  /*
  |--------------------------------------------------------------------------
  | MUTATORS
  |--------------------------------------------------------------------------
  */        
}