<?php

return [
  'actions' => [
    'view_referrals' => 'Empfehlungen anzeigen',
    'open_transactions' => 'Transaktionen öffnen',
    'open_orders' => 'Bestellungen anzeigen',
  ],
  'events' => [
    'referral' => [
      'attached' => [
        'name' => 'Neue Empfehlung',
        'title' => 'Neue Empfehlung',
        'excerpt' => '{{referral_name}} ist über Ihren Link beigetreten.',
        'body' => '{{referral_name}} hat sich über Ihren Referral-Link registriert.',
      ],
    ],
    'wallet' => [
      'reward' => [
        'created' => [
          'name' => 'Prämie gutgeschrieben',
          'title' => 'Prämie gutgeschrieben',
          'excerpt' => '{{amount}} {{currency}} gutgeschrieben.',
          'body' => 'Sie haben {{amount}} {{currency}} für {{trigger_label}} erhalten.',
        ],
      ],
    ],
    'withdrawal' => [
      'approved' => [
        'name' => 'Auszahlungsantrag genehmigt',
        'title' => 'Auszahlungsantrag genehmigt',
        'excerpt' => 'Auszahlungsantrag #{{withdrawal_id}} über {{amount}} {{currency}} wurde genehmigt.',
        'body' => 'Ihr Auszahlungsantrag #{{withdrawal_id}} wurde genehmigt und wird bearbeitet.',
      ],
      'paid' => [
        'name' => 'Auszahlungsantrag ausgezahlt',
        'title' => 'Auszahlungsantrag ausgezahlt',
        'excerpt' => 'Auszahlungsantrag #{{withdrawal_id}} über {{amount}} {{currency}} wurde ausgezahlt.',
        'body' => 'Ihr Auszahlungsantrag #{{withdrawal_id}} wurde ausgezahlt.',
      ],
    ],
    'review' => [
      'published' => [
        'name' => 'Bewertung veröffentlicht',
        'title' => 'Bewertung veröffentlicht',
        'excerpt' => 'Ihre Bewertung #{{review_id}} wurde veröffentlicht.',
        'body' => 'Ihre Bewertung für {{reviewable_type}} {{reviewable_name}} ist für andere sichtbar.',
      ],
    ],
    'order' => [
      'status' => [
        'changed' => [
          'name' => 'Bestellstatus geändert',
          'title' => 'Bestellstatus aktualisiert',
          'excerpt' => 'Bestellung #{{order_code}} Status: {{status}}.',
          'body' => 'Der Status der Bestellung #{{order_code}} wurde von {{previous_status}} auf {{status}} geändert.',
        ],
      ],
      'payment' => [
        'changed' => [
          'name' => 'Zahlungsstatus geändert',
          'title' => 'Zahlungsstatus aktualisiert',
          'excerpt' => 'Bestellung #{{order_code}} Zahlungsstatus: {{status}}.',
          'body' => 'Der Zahlungsstatus der Bestellung #{{order_code}} wurde von {{previous_status}} auf {{status}} geändert.',
        ],
      ],
      'delivery' => [
        'changed' => [
          'name' => 'Lieferstatus geändert',
          'title' => 'Lieferstatus aktualisiert',
          'excerpt' => 'Bestellung #{{order_code}} Lieferstatus: {{status}}.',
          'body' => 'Der Lieferstatus der Bestellung #{{order_code}} wurde von {{previous_status}} auf {{status}} geändert.',
        ],
      ],
    ],
  ],
];
