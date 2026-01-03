<?php

return [
  'actions' => [
    'view_referrals' => 'Открыть рефералов',
    'open_transactions' => 'Открыть транзакции',
    'open_orders' => 'Открыть заказы',
  ],
  'events' => [
    'referral' => [
      'attached' => [
        'name' => 'Новый реферал',
        'title' => 'Новый реферал',
        'excerpt' => '{{referral_name}} присоединился по вашей ссылке.',
        'body' => '{{referral_name}} зарегистрировался по вашей реферальной ссылке.',
      ],
    ],
    'wallet' => [
      'reward' => [
        'created' => [
          'name' => 'Вознаграждение начислено',
          'title' => 'Вознаграждение начислено',
          'excerpt' => 'Зачислено {{amount}} {{currency}}.',
          'body' => 'Вы получили {{amount}} {{currency}} за {{trigger_label}}.',
        ],
      ],
    ],
    'withdrawal' => [
      'approved' => [
        'name' => 'Заявка на вывод одобрена',
        'title' => 'Заявка на вывод одобрена',
        'excerpt' => 'Заявка на вывод №{{withdrawal_id}} на сумму {{amount}} {{currency}} одобрена.',
        'body' => 'Ваша заявка на вывод №{{withdrawal_id}} одобрена и находится в обработке.',
      ],
      'paid' => [
        'name' => 'Заявка на вывод выплачена',
        'title' => 'Заявка на вывод выплачена',
        'excerpt' => 'Заявка на вывод №{{withdrawal_id}} на сумму {{amount}} {{currency}} выплачена.',
        'body' => 'Ваша заявка на вывод №{{withdrawal_id}} выплачена.',
      ],
    ],
    'review' => [
      'published' => [
        'name' => 'Отзыв опубликован',
        'title' => 'Отзыв опубликован',
        'excerpt' => 'Ваш отзыв №{{review_id}} опубликован.',
        'body' => 'Ваш отзыв о {{reviewable_type}} {{reviewable_name}} виден другим.',
      ],
    ],
    'order' => [
      'status' => [
        'changed' => [
          'name' => 'Статус заказа изменен',
          'title' => 'Статус заказа обновлен',
          'excerpt' => 'Заказ №{{order_code}}: статус {{status}}.',
          'body' => 'Статус заказа №{{order_code}} изменен с {{previous_status}} на {{status}}.',
        ],
      ],
      'payment' => [
        'changed' => [
          'name' => 'Статус оплаты изменен',
          'title' => 'Статус оплаты обновлен',
          'excerpt' => 'Заказ №{{order_code}}: статус оплаты {{status}}.',
          'body' => 'Статус оплаты изменен с {{previous_status}} на {{status}} для заказа №{{order_code}}.',
        ],
      ],
      'delivery' => [
        'changed' => [
          'name' => 'Статус доставки изменен',
          'title' => 'Статус доставки обновлен',
          'excerpt' => 'Заказ №{{order_code}}: статус доставки {{status}}.',
          'body' => 'Статус доставки изменен с {{previous_status}} на {{status}} для заказа №{{order_code}}.',
        ],
      ],
    ],
  ],
];
