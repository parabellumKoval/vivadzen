<?php

return [
  'new_order' => 'Order placed',
  'new_order_admin' => 'New order',
  'all_rights_reserved' => 'All rights reserved',
  'thank_for_order' => 'Thank you for your order!',

  'referral' => [
    'new_sponsor_subject' => 'You have a new referral',
    'new_sponsor_title' => 'New referral in your team',
    'new_sponsor_intro' => 'Congratulations, :name! A new member just joined your team.',
    'new_sponsor_button' => 'Open dashboard',
    'details_title' => 'Referral details',
    'sponsor_fallback' => 'partner',
    'labels' => [
      'name' => 'Name',
      'email' => 'Email',
      'phone' => 'Phone',
      'registered_at' => 'Registration date',
      'referral_code' => 'Referral code',
    ],
  ],

  'reward' => [
    'credit_subject' => 'Bonus credited',
    'debit_subject' => 'Bonus debited',
    'title' => [
      'credit' => 'Credited :amount',
      'debit' => 'Debited :amount',
    ],
    'intro' => [
      'credit' => 'Your bonus wallet has been credited with :amount.',
      'debit' => 'Your bonus wallet has been debited by :amount.',
    ],
    'reversal_notice' => 'This operation reverses a previously issued reward.',
    'details_title' => 'Operation details',
    'balance_line' => 'Current balance: :balance.',
    'footer_note' => 'You can review the full history in your account.',
    'button' => 'Open dashboard',
    'labels' => [
      'trigger' => 'Trigger',
      'trigger_key' => 'Trigger key',
      'description' => 'Description',
      'external_id' => 'External ID',
      'beneficiary_type' => 'Beneficiary type',
      'level' => 'Level',
      'order_id' => 'Order number',
      'order_total' => 'Order total',
      'review_id' => 'Review ID',
      'rating' => 'Rating',
      'reference_type' => 'Operation type',
      'reference_id' => 'Operation ID',
      'balance' => 'Balance after operation',
    ],
    'beneficiaries' => [
      'actor' => 'Participant',
      'upline' => 'Upline',
    ],
  ],

  'withdrawal' => [
    'approved_subject' => 'Withdrawal request #:id approved',
    'approved_title' => 'Withdrawal request #:id approved',
    'paid_subject' => 'Withdrawal request #:id paid',
    'paid_title' => 'Withdrawal request #:id paid',
    'approved_intro' => 'We approved your withdrawal request #:id. The payout will be processed soon.',
    'paid_intro' => 'Your withdrawal request #:id has been paid. Thank you for staying with us.',
    'details_title' => 'Request details',
    'button' => 'Open dashboard',
    'labels' => [
      'amount' => 'Payout amount',
      'wallet_amount' => 'Deducted from wallet',
      'status' => 'Status',
      'method' => 'Payout method',
      'approved_at' => 'Approved at',
      'paid_at' => 'Paid at',
      'requested_at' => 'Created at',
      'fx_rate' => 'Conversion rate',
    ],
    'statuses' => [
      'pending' => 'In progress',
      'approved' => 'Approved',
      'rejected' => 'Rejected',
      'paid' => 'Paid',
    ],
  ],
];
