<?php

return [
  'new_order' => 'Objednávka vytvořena',
  'new_order_admin' => 'Nová objednávka',
  'all_rights_reserved' => 'Všechna práva vyhrazena',
  'thank_for_order' => 'Děkujeme za objednávku v našem obchodě!',
  'order_admin' => [
    'title' => 'Nová objednávka: :code',
    'button' => 'Zobrazit objednávku',
  ],

  'referral' => [
    'new_sponsor_subject' => 'Máte nové doporučení',
    'new_sponsor_title' => 'Nový referral ve vašem týmu',
    'new_sponsor_intro' => 'Gratulujeme, :name! Do vašeho týmu se přidal nový člen.',
    'new_sponsor_button' => 'Otevřít administraci',
    'details_title' => 'Detaily referralů',
    'sponsor_fallback' => 'partner',
    'labels' => [
      'name' => 'Jméno',
      'email' => 'Email',
      'phone' => 'Telefon',
      'registered_at' => 'Datum registrace',
      'referral_code' => 'Referral kód',
    ],
  ],

  'reward' => [
    'credit_subject' => 'Bonus připsán',
    'debit_subject' => 'Bonus odepsán',
    'title' => [
      'credit' => 'Připsáno :amount',
      'debit' => 'Odečteno :amount',
    ],
    'intro' => [
      'credit' => 'Na váš bonusový účet bylo připsáno :amount.',
      'debit' => 'Z vašeho bonusového účtu bylo odečteno :amount.',
    ],
    'reversal_notice' => 'Tato operace stornuje dříve vyplacenou odměnu.',
    'details_title' => 'Detaily operace',
    'balance_line' => 'Aktuální zůstatek: :balance.',
    'footer_note' => 'Celou historii najdete ve svém účtu.',
    'button' => 'Otevřít administraci',
    'labels' => [
      'trigger' => 'Trigger',
      'trigger_key' => 'Kód triggeru',
      'description' => 'Popis',
      'external_id' => 'Externí ID',
      'beneficiary_type' => 'Typ příjemce',
      'level' => 'Úroveň',
      'order_id' => 'Číslo objednávky',
      'order_total' => 'Hodnota objednávky',
      'review_id' => 'ID recenze',
      'rating' => 'Hodnocení',
      'reference_type' => 'Typ operace',
      'reference_id' => 'ID operace',
      'balance' => 'Zůstatek po operaci',
    ],
    'beneficiaries' => [
      'actor' => 'Účastník',
      'upline' => 'Upline',
    ],
  ],

  'withdrawal' => [
    'approved_subject' => 'Žádost o výběr č. :id schválena',
    'approved_title' => 'Žádost o výběr č. :id schválena',
    'paid_subject' => 'Žádost o výběr č. :id vyplacena',
    'paid_title' => 'Žádost o výběr č. :id vyplacena',
    'approved_intro' => 'Vaši žádost č. :id jsme schválili. Výplatu připravíme co nejdříve.',
    'paid_intro' => 'Vaše žádost č. :id byla vyplacena. Děkujeme za spolupráci.',
    'details_title' => 'Detaily žádosti',
    'button' => 'Otevřít administraci',
    'labels' => [
      'amount' => 'Částka k výplatě',
      'wallet_amount' => 'Strženo z peněženky',
      'status' => 'Stav',
      'method' => 'Způsob výplaty',
      'approved_at' => 'Datum schválení',
      'paid_at' => 'Datum výplaty',
      'requested_at' => 'Datum vytvoření',
      'fx_rate' => 'Směnný kurz',
    ],
    'statuses' => [
      'pending' => 'Zpracovává se',
      'approved' => 'Schváleno',
      'rejected' => 'Zamítnuto',
      'paid' => 'Vyplaceno',
    ],
  ],

  'verify_email' => [
    'subject' => 'Ověřte svou e-mailovou adresu',
    'greeting' => 'Vítejte!',
    'intro' => 'Prosím, klikněte na tlačítko níže a ověřte svou e-mailovou adresu.',
    'button' => 'Ověřit e-mail',
    'outro' => 'Pokud jste si nevytvořili účet, prosím ignorujte tuto zprávu.',
  ],
];
