<?php

return [
  'actions' => [
    'view_referrals' => 'Переглянути рефералів',
    'open_transactions' => 'Відкрити транзакції',
    'open_orders' => 'Відкрити замовлення',
  ],
  'events' => [
    'referral' => [
      'attached' => [
        'name' => 'Новий реферал',
        'title' => 'Новий реферал',
        'excerpt' => '{{referral_name}} приєднався за вашим посиланням.',
        'body' => '{{referral_name}} зареєструвався за вашим реферальним посиланням.',
      ],
    ],
    'wallet' => [
      'reward' => [
        'created' => [
          'name' => 'Винагороду нараховано',
          'title' => 'Винагороду нараховано',
          'excerpt' => 'Нараховано {{amount}} {{currency}}.',
          'body' => 'Ви отримали {{amount}} {{currency}} за {{trigger_label}}.',
        ],
      ],
    ],
    'withdrawal' => [
      'approved' => [
        'name' => 'Заявка на виведення схвалена',
        'title' => 'Заявка на виведення схвалена',
        'excerpt' => 'Заявку на виведення №{{withdrawal_id}} на суму {{amount}} {{currency}} схвалено.',
        'body' => 'Ваша заявка на виведення №{{withdrawal_id}} схвалена і обробляється.',
      ],
      'paid' => [
        'name' => 'Заявка на виведення виплачена',
        'title' => 'Заявка на виведення виплачена',
        'excerpt' => 'Заявку на виведення №{{withdrawal_id}} на суму {{amount}} {{currency}} виплачено.',
        'body' => 'Ваша заявка на виведення №{{withdrawal_id}} виплачена.',
      ],
    ],
    'review' => [
      'published' => [
        'name' => 'Відгук опубліковано',
        'title' => 'Відгук опубліковано',
        'excerpt' => 'Ваш відгук №{{review_id}} опубліковано.',
        'body' => 'Ваш відгук про {{reviewable_type}} {{reviewable_name}} видно іншим.',
      ],
    ],
    'order' => [
      'status' => [
        'changed' => [
          'name' => 'Статус замовлення змінено',
          'title' => 'Статус замовлення оновлено',
          'excerpt' => 'Замовлення №{{order_code}}: статус {{status}}.',
          'body' => 'Статус замовлення №{{order_code}} змінено з {{previous_status}} на {{status}}.',
        ],
      ],
      'payment' => [
        'changed' => [
          'name' => 'Статус оплати змінено',
          'title' => 'Статус оплати оновлено',
          'excerpt' => 'Замовлення №{{order_code}}: статус оплати {{status}}.',
          'body' => 'Статус оплати змінено з {{previous_status}} на {{status}} для замовлення №{{order_code}}.',
        ],
      ],
      'delivery' => [
        'changed' => [
          'name' => 'Статус доставки змінено',
          'title' => 'Статус доставки оновлено',
          'excerpt' => 'Замовлення №{{order_code}}: статус доставки {{status}}.',
          'body' => 'Статус доставки змінено з {{previous_status}} на {{status}} для замовлення №{{order_code}}.',
        ],
      ],
    ],
  ],
];
