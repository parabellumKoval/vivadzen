<?php
	
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use Daaner\NovaPoshta\Models\Counterparty;
use Daaner\NovaPoshta\Models\Address;

class NovaposhtaController extends Controller
{

  public function counterpartyContacts(Request $request) {
    $cp = new Counterparty;

    //если список менее 100 человек - пагинация не обязательна
    $cp->setPage(1);
    $cp->setLimit(100);

    $agent = $cp->getCounterpartyContactPerson('489b9bff-7926-11ec-8513-b88303659df5');

    return $agent;
  }

	public function addressList(Request $request) {
    $cp = new Counterparty;

    //return $request->form;
    //по умолчанию Recipient
    $cp->setLimit(100);

    $cp->setCounterpartyProperty('Sender'); // или насильно Recipient
    $addresses = $cp->getCounterpartyAddresses($request->form['sender']);

    // dd($addresses);

    return $addresses;
  }


	public function contactsList(Request $request) {
    $cp = new Counterparty;

    $cp->setLimit(100);
    $contacts = $cp->getCounterpartyContactPerson($request->form['sender']);

    return $contacts;
  }


	public function counterpartyList(Request $request) {
    $cp = new Counterparty;

    //если список менее 100 человек - пагинация не обязательна
    $cp->setPage(1);
    $cp->setLimit(100);

    //если значение не указано - по умолчанию будет `Recipient`
    $cp->setCounterpartyProperty('Sender');
    $agent = $cp->getCounterparties();

    dd($agent);
    return $agent;
    //с поиском (поиск не ищет вхождения. "Турфирма" найдется по "турф", но не найдется по "фирма")
    //$agent = $cp->getCounterparties('Талісман');
  }


  public function settlementFind(Request $request) {
    $adr = new Address;
    $adr->setLimit(100);

    $cities = $adr->getSettlements($request->q);

    return $cities;
  }


  public function cityFind(Request $request) {
    $adr = new Address;
    $adr->setLimit(100);

    if($request->q)
      $cities = $adr->getCities($request->q);
    elseif($request->ref)
      $cities = $adr->getCities($request->ref, false);
    else
      $cities = [];

    return $cities;
  }


  public function warehouseFind(Request $request) {
    $adr = new Address;
    $adr->setLimit(100);

    // $adr->filterPostFinance();
    // $adr->setTypeOfWarehouseRef('9a68df70-0267-42a8-bb5c-37f427e36ee4');
    
    $warehouses = $adr->getWarehouseSettlements($request->form['RecipientCityName']);

    return $warehouses;
  }

	public function streetFind(Request $request) {
    $adr = new Address;
    $adr->setLimit(100);

    $results = $adr->searchSettlementStreets($request->form['RecipientCityName'], $request->q);
    $streets = $results['result'][0]['Addresses'] ?? null;

    return [
      'result' => $streets
    ];
  }
}