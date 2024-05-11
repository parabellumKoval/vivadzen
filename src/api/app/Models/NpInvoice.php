<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Log;

use Daaner\NovaPoshta\Models\Address;
use App\Models\Daaner\Address as MyAddress;
use Daaner\NovaPoshta\Models\Counterparty;
use Daaner\NovaPoshta\Models\InternetDocument;
use App\Models\Daaner\InternetDocument as MyInternetDocument;

use App\Models\Order;

class NpInvoice extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'np_invoices';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = ['order_id', 'fields', 'invoice', 'status'];
    // protected $hidden = [];
    // protected $dates = [];

    protected $casts = [
      'fields' => 'array',
      'invoice' => 'array'
    ];

    protected $fakeColumns = [
      'fields',
      'invoice'
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    // public function __construct(){

    // }
      
    public function toArray()
    {
      return [
        'id' => $this->id,
        'price' => $this->price,
      ];
    }

    public static function getInternetDocument($ref) {

      $intDoc = new InternetDocument;
      $data = $intDoc->getPDF($ref, true);
      return $data;
    }

    public function makeInvoice($crud = true)
    {
      if(empty($this->invoice['Ref'])) {
        return "<button class='btn btn-default btn-sm' disabled>PDF</button>";
      }else {
        $link = "/admin/np-invoice/pdf/{$this->id}";
        return "<a class='btn btn-primary btn-sm' target='_blank' href='{$link}'>PDF</a>";
      }
    }

    public static function getCounterpartyAddresses($sender_ref) {
      $cp = new Counterparty;
      
      $cp->setCounterpartyProperty('Sender'); // или насильно Recipient
      $agent = $cp->getCounterpartyOptions($sender_ref);

      dd($agent);
    }

    public static function getWarehouseByRef($city_ref = null, $warehouse_ref = null) {
      $adr = new MyAddress;
      $warehouses = $adr->getWarehouses($city_ref, false, $warehouse_ref);

      if(!empty($warehouses['result']))
        return $warehouses['result'][0];
      else
        return null;
    }


    public static function getWarehouseSettlements($city_ref = null, $warehouse_ref = null) {
      $adr = new MyAddress;
      $warehouses = $adr->getWarehouses(null, null, $warehouse_ref, $city_ref);

      if(!empty($warehouses['result']))
        return $warehouses['result'][0];
      else
        return null;
    }

    public static function getWarehouse($city_name, $warehouse_name, $city_ref = null) {
      $adr = new Address;
      
      if($city_ref) {
        $warehouses = $adr->getWarehouses($ref, false);
      }else {
        $warehouses = $adr->getWarehouses($city_name);
      }

      // dd($warehouses);
      if(!empty($warehouses['result'])){
        $key = array_search($warehouse_name, array_column($warehouses['result'], 'Description'));
        $warehouse = $key !== false? $warehouses['result'][$key] : null;
      }else {
        $warehouse = null;
      }

      return $warehouse;
    }

    public static function getStreet($city_ref, $street_name, $street_ref = null) {
      if(!$city_ref || !$street_name)
        return null;

      $adr = new Address;

      $results = $adr->searchSettlementStreets($city_ref, $street_name);
      $streets = $results['result'][0]['Addresses'] ?? null;
      
      if(!$streets)
        return null;

      if($street_ref) {
        $key = array_search($street_ref, array_column($streets, 'SettlementStreetRef'));
        $street = $key !== false? $streets[$key] : null;
      }else {
        $street = $streets[0];
      }
      
      return $street;
    }

    public static function getCity($ref = null, $q = null) {
      $adr = new Address;

      if($ref) {
        // $cities = $adr->getCities($ref, false);
        $adr->filterRef($ref);
        $cities = $adr->getSettlements();

      }elseif($q)
        $cities = $adr->getCities($q);
      else
        $cities = [];

        // dd($cities);
      $city = $cities['result'][0] ?? null;
      return $city;
    }

    public static function getContactSender($sender_ref, $contact_ref) {
      $cp = new Counterparty;
      $contacts = $cp->getCounterpartyContactPerson($sender_ref);

      if(!empty($contacts['result'])){
        $key = array_search($contact_ref, array_column($contacts['result'], 'Ref'));
        $contact = $key !== false? $contacts['result'][$key] : null;
      }else {
        $contact = null;
      }

      return $contact;
    }

    public static function getSenderAddress($sender_ref, $address_ref) {
      $cp = new Counterparty;
  
      $cp->setCounterpartyProperty('Sender');
      $addresses = $cp->getCounterpartyAddresses($sender_ref);
      if(!empty($addresses['result'])){
        $key = array_search($address_ref, array_column($addresses['result'], 'Ref'));
        $address = $key !== false? $addresses['result'][$key] : null;
      }else {
        $address = null;
      }

      return $address;
    }

    public static function getSenders() {
      $cp = new Counterparty;
  
      //если список менее 100 человек - пагинация не обязательна
      $cp->setPage(1);
      // $cp->setLimit(100);
  
      // Organization, PrivatePerson
      //$cp->setCounterpartyType('PrivatePerson');
      // Recipient, Sender, ThirdPerson
      $cp->setCounterpartyProperty('Sender');
      $agent = $cp->getCounterparties();
    
      return $agent['result'];
      //с поиском (поиск не ищет вхождения. "Турфирма" найдется по "турф", но не найдется по "фирма")
      //$agent = $cp->getCounterparties('Талісман');
    }

    public static function createInvoice($data)
    {
      $np = new MyInternetDocument;

      if(!empty($data['PayerType']))
        $np->setPayerType($data['PayerType']);

      if(!empty($data['PaymentMethod']))
        $np->setPaymentMethod($data['PaymentMethod']);
      
      if(!empty($data['DateTime']))
        $np->setDateTime($data['DateTime']);
      
      if(!empty($data['CargoType']))
        $np->setCargoType($data['CargoType']);
      
      if(!empty($data['Weight']))
        $np->setWeight($data['Weight']);
      
      if(!empty($data['ServiceType']))
        $np->setServiceType($data['ServiceType']);
      
      if(!empty($data['SeatsAmount']))
        $np->setSeatsAmount($data['SeatsAmount']);
      
      if(!empty($data['Description']))
        $np->setDescription($data['Description']);
      
      if(!empty($data['Cost']))
        $np->setCost($data['Cost']);

      // AfterpaymentOnGoodsCost
      if(!empty($data['IsAfterpayment']) && $data['IsAfterpayment'] && !empty($data['AfterpaymentOnGoodsCost']))
        $np->setBackwardDeliveryData($data['AfterpaymentOnGoodsCost']);

      $np->setOptionsSeat(2);

      $np->setSender([
        'Sender' => $data['sender'] ?? '',
        'CitySender' => $data['CitySender'] ?? '',
        'SenderAddress' => $data['SenderAddress'] ?? '',
        'ContactSender' => $data['ContactSender'] ?? '',
        'SendersPhone' => !empty($data['SenderPhone'])? self::normalizePhone($data['SenderPhone']): '',
      ]);
      
      // $np->setCustomRecipient([
      //   'RecipientsPhone' => $data['RecipientsPhone'],
      //   'RecipientName' => $data['RecipientName'],
      //   'CityRecipient' => $data['RecipientCityName'],
      //   'RecipientAddress' => $data['RecipientWarehouseIndex']
      //   // 'RecipientWarehouseIndex' => $data['RecipientWarehouseIndex'],
      // ]);

      $RecipientCity = self::getCity($data['RecipientCityName']);

      if(in_array($data['ServiceType'], ['DoorsWarehouse', 'WarehouseWarehouse'])) {
        $RecipientAddressName = self::getWarehouseSettlements($data['RecipientCityName'], $data['RecipientWarehouseIndex']);
        $RecipientAddressName = $RecipientAddressName['Number'];
      }elseif(in_array($data['ServiceType'], ['DoorsDoors', 'WarehouseDoors'])){
        // $RecipientAddressName = self::getStreet(null, null, $data['RecipientStreet']);
        $RecipientAddressName = $data['RecipientStreet'];
      }else{
        $RecipientAddressName = '';
      }

      $np->setRecipient([
        'RecipientsPhone' => !empty($data['RecipientsPhone'])? self::normalizePhone($data['RecipientsPhone']): '',
        'RecipientName' => $data['RecipientName'] ?? '',
        // City name
        'RecipientCityName' => $RecipientCity['Description'] ?? '',
        // Warehouse Number
        'RecipientAddressName' => $RecipientAddressName,
        'RecipientArea' => '',
        'RecipientAreaRegions' => '',
        'RecipientHouse' => $data['RecipientHouse'],
        'RecipientFlat' => $data['RecipientFlat'],
      ]);

      $code = rand(100000,999999);
      $ttn = $np->save("Заявка №{$code}");

      Log::channel('novaposhta')->info('TTH');
      Log::channel('novaposhta')->info(print_r($ttn,true));

      return $ttn;
    }

    public static function normalizePhone($value) {
      if(empty($value))
        return '';

      return preg_replace('/[^0-9.]+/', '', $value); 
    }
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
  
    /**
     * order
     *
     * Return related order
     * 
     * @return Order
     */
    public function order()
    {
      return $this->belongsTo(Order::class, 'order_id');
    }

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

    public function getDeliveryCostAttribute() {
      return !empty($this->invoice['CostOnSite'])? $this->invoice['CostOnSite'] . 'грн.': '–';
    }

    public function getEstimatedDateAttribute() {
      return !empty($this->invoice['EstimatedDeliveryDate'])? $this->invoice['EstimatedDeliveryDate']: '–';
    }

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    // public function setFieldsAttribute($v){
    //   dd(\Request::all());
    // }

    public function setInvoiceAttribute($v){
      // dd(\Request::get('invoice_data'));
      $this->attributes['invoice'] = json_encode(\Request::get('invoice_data'));
    }
}
