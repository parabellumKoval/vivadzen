<?php

return [
  'actions' => [
    'view_referrals' => 'Zobrazit referraly',
    'open_transactions' => 'Otevřít transakce',
    'open_orders' => 'Zobrazit objednávky',
  ],
  'events' => [
    'referral' => [
      'attached' => [
        'name' => 'Nový referral',
        'title' => 'Nový referral',
        'excerpt' => '{{referral_name}} se přidal pomocí vašeho odkazu.',
        'body' => '{{referral_name}} se zaregistroval pomocí vašeho referralového odkazu.',
      ],
    ],
    'wallet' => [
      'reward' => [
        'created' => [
          'name' => 'Odměna připsána',
          'title' => 'Odměna připsána',
          'excerpt' => 'Připsáno {{amount}} {{currency}}.',
          'body' => 'Získali jste {{amount}} {{currency}} za {{trigger_label}}.',
        ],
      ],
    ],
    'withdrawal' => [
      'approved' => [
        'name' => 'Žádost o výběr schválena',
        'title' => 'Žádost o výběr schválena',
        'excerpt' => 'Žádost o výběr č. {{withdrawal_id}} na {{amount}} {{currency}} byla schválena.',
        'body' => 'Vaše žádost o výběr č. {{withdrawal_id}} je schválena a zpracovává se.',
      ],
      'paid' => [
        'name' => 'Žádost o výběr vyplacena',
        'title' => 'Žádost o výběr vyplacena',
        'excerpt' => 'Žádost o výběr č. {{withdrawal_id}} na {{amount}} {{currency}} byla vyplacena.',
        'body' => 'Vaše žádost o výběr č. {{withdrawal_id}} byla vyplacena.',
      ],
    ],
    'review' => [
      'published' => [
        'name' => 'Recenze zveřejněna',
        'title' => 'Recenze zveřejněna',
        'excerpt' => 'Vaše recenze č. {{review_id}} je nyní zveřejněna.',
        'body' => 'Vaše recenze pro {{reviewable_type}} {{reviewable_name}} je viditelná pro ostatní.',
      ],
    ],
    'order' => [
      'status' => [
        'changed' => [
          'name' => 'Stav objednávky změněn',
          'title' => 'Stav objednávky aktualizován',
          'excerpt' => 'Objednávka č. {{order_code}} stav: {{status}}.',
          'body' => 'Stav objednávky č. {{order_code}} se změnil z {{previous_status}} na {{status}}.',
        ],
      ],
      'payment' => [
        'changed' => [
          'name' => 'Stav platby změněn',
          'title' => 'Stav platby aktualizován',
          'excerpt' => 'Objednávka č. {{order_code}} stav platby: {{status}}.',
          'body' => 'Stav platby se změnil z {{previous_status}} na {{status}} pro objednávku č. {{order_code}}.',
        ],
      ],
      'delivery' => [
        'changed' => [
          'name' => 'Stav doručení změněn',
          'title' => 'Stav doručení aktualizován',
          'excerpt' => 'Objednávka č. {{order_code}} stav doručení: {{status}}.',
          'body' => 'Stav doručení se změnil z {{previous_status}} na {{status}} pro objednávku č. {{order_code}}.',
        ],
      ],
    ],
  ],
];
