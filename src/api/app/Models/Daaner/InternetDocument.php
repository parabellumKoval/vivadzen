<?php
namespace App\Models\Daaner;

class InternetDocument extends \Daaner\NovaPoshta\Models\InternetDocument
{

  public function setCustomRecipient(array $Recipient): self
  {
    $this->methodProperties['RecipientsPhone'] = $Recipient['RecipientsPhone'];
    $this->methodProperties['RecipientName'] = $Recipient['RecipientName'];
    $this->methodProperties['CityRecipient'] = $Recipient['CityRecipient'];
    $this->methodProperties['RecipientAddress'] = $Recipient['RecipientAddress'];

    return $this;
  }
}