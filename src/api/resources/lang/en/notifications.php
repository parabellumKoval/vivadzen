<?php

return [
  'actions' => [
    'view_referrals' => 'View referrals',
    'open_transactions' => 'Open transactions',
    'open_orders' => 'Open orders',
  ],
  'events' => [
    'referral' => [
      'attached' => [
        'name' => 'New referral',
        'title' => 'New referral',
        'excerpt' => '{{referral_name}} joined using your link.',
        'body' => '{{referral_name}} registered via your referral link.',
      ],
    ],
    'wallet' => [
      'reward' => [
        'created' => [
          'name' => 'Reward credited',
          'title' => 'Reward credited',
          'excerpt' => '{{amount}} {{currency}} credited.',
          'body' => 'You received {{amount}} {{currency}} for {{trigger_label}}.',
        ],
      ],
    ],
    'withdrawal' => [
      'approved' => [
        'name' => 'Withdrawal approved',
        'title' => 'Withdrawal approved',
        'excerpt' => 'Withdrawal #{{withdrawal_id}} for {{amount}} {{currency}} was approved.',
        'body' => 'Your withdrawal request #{{withdrawal_id}} is approved and is being processed.',
      ],
      'paid' => [
        'name' => 'Withdrawal paid',
        'title' => 'Withdrawal paid',
        'excerpt' => 'Withdrawal #{{withdrawal_id}} for {{amount}} {{currency}} was paid.',
        'body' => 'Your withdrawal request #{{withdrawal_id}} has been paid out.',
      ],
    ],
    'review' => [
      'published' => [
        'name' => 'Review published',
        'title' => 'Review published',
        'excerpt' => 'Your review #{{review_id}} is now published.',
        'body' => 'Your review for {{reviewable_type}} {{reviewable_name}} is visible to others.',
      ],
    ],
    'order' => [
      'status' => [
        'changed' => [
          'name' => 'Order status changed',
          'title' => 'Order status updated',
          'excerpt' => 'Order #{{order_code}} status: {{status}}.',
          'body' => 'Order #{{order_code}} status changed from {{previous_status}} to {{status}}.',
        ],
      ],
      'payment' => [
        'changed' => [
          'name' => 'Payment status changed',
          'title' => 'Payment status updated',
          'excerpt' => 'Order #{{order_code}} payment status: {{status}}.',
          'body' => 'Payment status changed from {{previous_status}} to {{status}} for order #{{order_code}}.',
        ],
      ],
      'delivery' => [
        'changed' => [
          'name' => 'Delivery status changed',
          'title' => 'Delivery status updated',
          'excerpt' => 'Order #{{order_code}} delivery status: {{status}}.',
          'body' => 'Delivery status changed from {{previous_status}} to {{status}} for order #{{order_code}}.',
        ],
      ],
    ],
  ],
];
