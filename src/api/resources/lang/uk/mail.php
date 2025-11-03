<?php

return [
  'new_order' => 'Замовлення оформлено',
  'new_order_admin' => 'Нове замовлення',
  'all_rights_reserved' => 'Усі права захищені',
  'thank_for_order' => 'Дякуємо за замовлення у нашому магазині!',

  'referral' => [
    'new_sponsor_subject' => 'У вас новий реферал',
    'new_sponsor_title' => 'Новий реферал у вашій команді',
    'new_sponsor_intro' => 'Вітаємо, :name! До вашої команди приєднався новий учасник.',
    'new_sponsor_button' => 'Відкрити кабінет',
    'details_title' => 'Інформація про реферала',
    'sponsor_fallback' => 'партнер',
    'labels' => [
      'name' => 'Імʼя',
      'email' => 'Email',
      'phone' => 'Телефон',
      'registered_at' => 'Дата реєстрації',
      'referral_code' => 'Реферальний код',
    ],
  ],

  'reward' => [
    'credit_subject' => 'Зарахування бонусів',
    'debit_subject' => 'Списання бонусів',
    'title' => [
      'credit' => 'Зараховано :amount',
      'debit' => 'Списано :amount',
    ],
    'intro' => [
      'credit' => 'На ваш бонусний рахунок зараховано :amount.',
      'debit' => 'З вашого бонусного рахунку списано :amount.',
    ],
    'reversal_notice' => 'Операція є сторно раніше нарахованої винагороди.',
    'details_title' => 'Деталі операції',
    'balance_line' => 'Поточний баланс: :balance.',
    'footer_note' => 'Повну історію операцій шукайте в особистому кабінеті.',
    'button' => 'Перейти в кабінет',
    'labels' => [
      'trigger' => 'Тригер',
      'trigger_key' => 'Код тригера',
      'description' => 'Опис',
      'external_id' => 'Зовнішній ідентифікатор',
      'beneficiary_type' => 'Тип отримувача',
      'level' => 'Рівень',
      'order_id' => 'Номер замовлення',
      'order_total' => 'Сума замовлення',
      'review_id' => 'ID відгуку',
      'rating' => 'Оцінка',
      'reference_type' => 'Тип операції',
      'reference_id' => 'ID операції',
      'balance' => 'Баланс після операції',
    ],
    'beneficiaries' => [
      'actor' => 'Учасник',
      'upline' => 'Аплайн',
    ],
  ],

  'withdrawal' => [
    'approved_subject' => 'Заявку на вивід №:id схвалено',
    'approved_title' => 'Заявку на вивід №:id схвалено',
    'paid_subject' => 'Заявку на вивід №:id виплачено',
    'paid_title' => 'Заявку на вивід №:id виплачено',
    'approved_intro' => 'Ми схвалили вашу заявку №:id. Очікуйте виплату найближчим часом.',
    'paid_intro' => 'Вашу заявку №:id виплачено. Дякуємо, що користуєтесь нашою програмою.',
    'details_title' => 'Деталі заявки',
    'button' => 'Відкрити кабінет',
    'labels' => [
      'amount' => 'Сума до виплати',
      'wallet_amount' => 'Списано з гаманця',
      'status' => 'Статус',
      'method' => 'Спосіб виплати',
      'approved_at' => 'Дата схвалення',
      'paid_at' => 'Дата виплати',
      'requested_at' => 'Дата створення',
      'fx_rate' => 'Курс конвертації',
    ],
    'statuses' => [
      'pending' => 'В обробці',
      'approved' => 'Схвалена',
      'rejected' => 'Відхилена',
      'paid' => 'Виплачена',
    ],
  ],
];
