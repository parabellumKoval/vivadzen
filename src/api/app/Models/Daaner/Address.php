<?php
namespace App\Models\Daaner;

class Address extends \Daaner\NovaPoshta\Models\Address
{
    /**
     * Получение списка отделений и почтоматов в городах.
     * Работает без авторизации.
     *
     * @see https://developers.novaposhta.ua/view/model/a0cf0f5f-8512-11ec-8ced-005056b2dbe1/method/a2322f38-8512-11ec-8ced-005056b2dbe1 Получение списка отделений и почтоматов в городах
     * @since 2022-11-03
     *
     * @param  string|null  $cityRef  Строка или Ref поиска
     * @param  bool|null  $searchByString  Поиск по Ref = false или по строке
     * @return array
     */
    public function getWarehouses(?string $cityRef = null, ?bool $searchByString = true, ?string $ref = null, ?string $settlementRef = null): array
    {
      $this->calledMethod = 'getWarehouses';

      $this->getLimit();
      $this->getPage();
      $this->getTypeOfWarehouseRef();

      /**
       * Если значения пустые - вставляем насильно.
       */
      if (! $this->limit) {
          $this->methodProperties['Limit'] = config('novaposhta.page_limit');
      }

      if ($cityRef) {
          if ($searchByString) {
              $this->methodProperties['CityName'] = $cityRef;
          } else {
              $this->methodProperties['CityRef'] = $cityRef;
          }
      }

      if($settlementRef)
        $this->methodProperties['SettlementRef'] = $settlementRef;

      if($ref)
        $this->methodProperties['Ref'] = $ref;

      return $this->getResponse($this->model, $this->calledMethod, $this->methodProperties, false);
    }
}