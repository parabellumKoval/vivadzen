<?php

return [
  'new_order' => 'Pedido realizado',
  'new_order_admin' => 'Nuevo pedido',
  'all_rights_reserved' => 'Todos los derechos reservados',
  'thank_for_order' => '¡Gracias por tu compra!',
  'order_admin' => [
    'title' => 'Nuevo pedido: :code',
    'button' => 'Ver pedido',
  ],

  'referral' => [
    'new_sponsor_subject' => 'Tienes un nuevo referido',
    'new_sponsor_title' => 'Nuevo referido en tu equipo',
    'new_sponsor_intro' => 'Felicitaciones, :name. Un nuevo miembro se unió a tu equipo.',
    'new_sponsor_button' => 'Abrir panel',
    'details_title' => 'Detalles del referido',
    'sponsor_fallback' => 'socio',
    'labels' => [
      'name' => 'Nombre',
      'email' => 'Correo electrónico',
      'phone' => 'Teléfono',
      'registered_at' => 'Fecha de registro',
      'referral_code' => 'Código de referido',
    ],
  ],

  'reward' => [
    'credit_subject' => 'Bono acreditado',
    'debit_subject' => 'Bono debitado',
    'title' => [
      'credit' => 'Acreditado :amount',
      'debit' => 'Debitado :amount',
    ],
    'intro' => [
      'credit' => 'Tu billetera de bonos recibió :amount.',
      'debit' => 'Se debitó :amount de tu billetera de bonos.',
    ],
    'reversal_notice' => 'Esta operación revierte una recompensa emitida anteriormente.',
    'details_title' => 'Detalles de la operación',
    'balance_line' => 'Saldo actual: :balance.',
    'footer_note' => 'Puedes revisar todo el historial en tu cuenta.',
    'button' => 'Abrir panel',
    'labels' => [
      'trigger' => 'Disparador',
      'trigger_key' => 'Clave del disparador',
      'description' => 'Descripción',
      'external_id' => 'ID externo',
      'beneficiary_type' => 'Tipo de beneficiario',
      'level' => 'Nivel',
      'order_id' => 'Número de pedido',
      'order_total' => 'Total del pedido',
      'review_id' => 'ID del review',
      'rating' => 'Calificación',
      'reference_type' => 'Tipo de operación',
      'reference_id' => 'ID de la operación',
      'balance' => 'Saldo después de la operación',
    ],
    'beneficiaries' => [
      'actor' => 'Participante',
      'upline' => 'Upline',
    ],
  ],

  'withdrawal' => [
    'approved_subject' => 'Solicitud de retiro Nº:id aprobada',
    'approved_title' => 'Solicitud de retiro Nº:id aprobada',
    'paid_subject' => 'Solicitud de retiro Nº:id pagada',
    'paid_title' => 'Solicitud de retiro Nº:id pagada',
    'approved_intro' => 'Aprobamos tu solicitud Nº:id. Procesaremos el pago pronto.',
    'paid_intro' => 'Tu solicitud Nº:id ha sido pagada. Gracias por usar nuestro programa.',
    'details_title' => 'Detalles de la solicitud',
    'button' => 'Abrir panel',
    'labels' => [
      'amount' => 'Monto a pagar',
      'wallet_amount' => 'Debitado de la billetera',
      'status' => 'Estado',
      'method' => 'Método de pago',
      'approved_at' => 'Fecha de aprobación',
      'paid_at' => 'Fecha de pago',
      'requested_at' => 'Fecha de creación',
      'fx_rate' => 'Tipo de cambio',
    ],
    'statuses' => [
      'pending' => 'En proceso',
      'approved' => 'Aprobada',
      'rejected' => 'Rechazada',
      'paid' => 'Pagada',
    ],
  ],

  'landing_promo' => [
    'subject' => 'Tu código promocional para comprar en la tienda Vivadzen',
    'title' => 'Código promocional para compras en tienda',
    'intro' => '¡Gracias por tu solicitud! Usa este código promocional al comprar en la tienda.',
    'code_label' => 'Tu código promocional',
    'code_hint' => 'Muestra este código al vendedor al pagar.',
    'button' => 'Visitar el sitio',
  ],

  'verify_email' => [
    'subject' => 'Verifica tu dirección de correo electrónico',
    'greeting' => '¡Bienvenido!',
    'intro' => 'Por favor, haz clic en el botón de abajo para verificar tu dirección de correo electrónico.',
    'button' => 'Verificar correo electrónico',
    'outro' => 'Si no creaste una cuenta, ignora este mensaje.',
  ],
];
