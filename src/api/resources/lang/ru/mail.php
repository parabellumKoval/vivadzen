<?php

return [
  'new_order' => 'Заказ оформлен',
  'new_order_admin' => 'Новый заказ',
  'all_rights_reserved' => 'Все права защищены',
  'thank_for_order' => 'Спасибо за заказ в нашем магазине!',

  'referral' => [
    'new_sponsor_subject' => 'У вас новый реферал',
    'new_sponsor_title' => 'Новый реферал в вашей команде',
    'new_sponsor_intro' => 'Поздравляем, :name! В вашу команду добавлен новый участник.',
    'new_sponsor_button' => 'Открыть кабинет',
    'details_title' => 'Информация о реферале',
    'sponsor_fallback' => 'партнёр',
    'labels' => [
      'name' => 'Имя',
      'email' => 'Email',
      'phone' => 'Телефон',
      'registered_at' => 'Дата регистрации',
      'referral_code' => 'Реферальный код',
    ],
  ],

  'reward' => [
    'credit_subject' => 'Зачисление бонусов',
    'debit_subject' => 'Списание бонусов',
    'title' => [
      'credit' => 'Зачислено :amount',
      'debit' => 'Списано :amount',
    ],
    'intro' => [
      'credit' => 'На ваш бонусный счёт зачислено :amount.',
      'debit' => 'С вашего бонусного счёта списано :amount.',
    ],
    'reversal_notice' => 'Операция является сторно ранее выплаченного вознаграждения.',
    'details_title' => 'Детали операции',
    'balance_line' => 'Текущий баланс: :balance.',
    'footer_note' => 'Полная история операций доступна в личном кабинете.',
    'button' => 'Перейти в кабинет',
    'labels' => [
      'trigger' => 'Триггер',
      'trigger_key' => 'Код триггера',
      'description' => 'Описание',
      'external_id' => 'Внешний идентификатор',
      'beneficiary_type' => 'Тип получателя',
      'level' => 'Уровень',
      'order_id' => 'Номер заказа',
      'order_total' => 'Сумма заказа',
      'review_id' => 'ID отзыва',
      'rating' => 'Оценка',
      'reference_type' => 'Тип операции',
      'reference_id' => 'ID операции',
      'balance' => 'Баланс после операции',
    ],
    'beneficiaries' => [
      'actor' => 'Участник',
      'upline' => 'Аплайн',
    ],
  ],

  'withdrawal' => [
    'approved_subject' => 'Заявка на вывод №:id одобрена',
    'approved_title' => 'Заявка на вывод №:id одобрена',
    'paid_subject' => 'Заявка на вывод №:id выплачена',
    'paid_title' => 'Заявка на вывод №:id выплачена',
    'approved_intro' => 'Мы одобрили вашу заявку №:id. Подготовим выплату в ближайшее время.',
    'paid_intro' => 'Ваша заявка №:id выплачена. Спасибо, что пользуетесь нашей программой.',
    'details_title' => 'Детали заявки',
    'button' => 'Открыть кабинет',
    'labels' => [
      'amount' => 'Сумма к выплате',
      'wallet_amount' => 'Списано с кошелька',
      'status' => 'Статус',
      'method' => 'Способ выплаты',
      'approved_at' => 'Дата одобрения',
      'paid_at' => 'Дата выплаты',
      'requested_at' => 'Дата создания',
      'fx_rate' => 'Курс конвертации',
    ],
    'statuses' => [
      'pending' => 'В обработке',
      'approved' => 'Одобрена',
      'rejected' => 'Отклонена',
      'paid' => 'Выплачена',
    ],
  ],
];
