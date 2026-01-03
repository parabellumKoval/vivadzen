<?php

return [
  'actions' => [
    'view_referrals' => 'Ver referidos',
    'open_transactions' => 'Abrir transacciones',
    'open_orders' => 'Abrir pedidos',
  ],
  'events' => [
    'referral' => [
      'attached' => [
        'name' => 'Nuevo referido',
        'title' => 'Nuevo referido',
        'excerpt' => '{{referral_name}} se unió usando tu enlace.',
        'body' => '{{referral_name}} se registró mediante tu enlace de referido.',
      ],
    ],
    'wallet' => [
      'reward' => [
        'created' => [
          'name' => 'Bono acreditado',
          'title' => 'Bono acreditado',
          'excerpt' => '{{amount}} {{currency}} acreditado.',
          'body' => 'Recibiste {{amount}} {{currency}} por {{trigger_label}}.',
        ],
      ],
    ],
    'withdrawal' => [
      'approved' => [
        'name' => 'Solicitud de retiro aprobada',
        'title' => 'Solicitud de retiro aprobada',
        'excerpt' => 'La solicitud de retiro #{{withdrawal_id}} por {{amount}} {{currency}} fue aprobada.',
        'body' => 'Tu solicitud de retiro #{{withdrawal_id}} fue aprobada y se está procesando.',
      ],
      'paid' => [
        'name' => 'Solicitud de retiro pagada',
        'title' => 'Solicitud de retiro pagada',
        'excerpt' => 'La solicitud de retiro #{{withdrawal_id}} por {{amount}} {{currency}} fue pagada.',
        'body' => 'Tu solicitud de retiro #{{withdrawal_id}} ha sido pagada.',
      ],
    ],
    'review' => [
      'published' => [
        'name' => 'Reseña publicada',
        'title' => 'Reseña publicada',
        'excerpt' => 'Tu reseña #{{review_id}} ya está publicada.',
        'body' => 'Tu reseña de {{reviewable_type}} {{reviewable_name}} es visible para otros.',
      ],
    ],
    'order' => [
      'status' => [
        'changed' => [
          'name' => 'Estado del pedido cambiado',
          'title' => 'Estado del pedido actualizado',
          'excerpt' => 'Pedido #{{order_code}} estado: {{status}}.',
          'body' => 'El estado del pedido #{{order_code}} cambió de {{previous_status}} a {{status}}.',
        ],
      ],
      'payment' => [
        'changed' => [
          'name' => 'Estado del pago cambiado',
          'title' => 'Estado del pago actualizado',
          'excerpt' => 'Pedido #{{order_code}} estado del pago: {{status}}.',
          'body' => 'El estado del pago cambió de {{previous_status}} a {{status}} para el pedido #{{order_code}}.',
        ],
      ],
      'delivery' => [
        'changed' => [
          'name' => 'Estado de entrega cambiado',
          'title' => 'Estado de entrega actualizado',
          'excerpt' => 'Pedido #{{order_code}} estado de entrega: {{status}}.',
          'body' => 'El estado de entrega cambió de {{previous_status}} a {{status}} para el pedido #{{order_code}}.',
        ],
      ],
    ],
  ],
];
