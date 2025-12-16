<?php

return [
  'new_order' => 'Bestellung aufgegeben',
  'new_order_admin' => 'Neue Bestellung',
  'all_rights_reserved' => 'Alle Rechte vorbehalten',
  'thank_for_order' => 'Vielen Dank für Ihre Bestellung!',
  'order_admin' => [
    'title' => 'Neue Bestellung: :code',
    'button' => 'Bestellung anzeigen',
  ],

  'referral' => [
    'new_sponsor_subject' => 'Sie haben eine neue Empfehlung',
    'new_sponsor_title' => 'Neues Mitglied in Ihrem Team',
    'new_sponsor_intro' => 'Glückwunsch, :name! Ein neues Mitglied wurde Ihrem Team hinzugefügt.',
    'new_sponsor_button' => 'Zum Dashboard',
    'details_title' => 'Referral-Details',
    'sponsor_fallback' => 'Partner',
    'labels' => [
      'name' => 'Name',
      'email' => 'E-Mail',
      'phone' => 'Telefon',
      'registered_at' => 'Registrierungsdatum',
      'referral_code' => 'Referral-Code',
    ],
  ],

  'reward' => [
    'credit_subject' => 'Bonus gutgeschrieben',
    'debit_subject' => 'Bonus abgebucht',
    'title' => [
      'credit' => 'Gutgeschrieben: :amount',
      'debit' => 'Abgebucht: :amount',
    ],
    'intro' => [
      'credit' => 'Ihr Bonuskonto wurde mit :amount gutgeschrieben.',
      'debit' => 'Von Ihrem Bonuskonto wurden :amount abgebucht.',
    ],
    'reversal_notice' => 'Dieser Vorgang storniert eine zuvor ausgezahlte Prämie.',
    'details_title' => 'Vorgangsdetails',
    'balance_line' => 'Aktueller Stand: :balance.',
    'footer_note' => 'Die vollständige Historie finden Sie in Ihrem Konto.',
    'button' => 'Zum Dashboard',
    'labels' => [
      'trigger' => 'Trigger',
      'trigger_key' => 'Trigger-Schlüssel',
      'description' => 'Beschreibung',
      'external_id' => 'Externe ID',
      'beneficiary_type' => 'Empfängertyp',
      'level' => 'Level',
      'order_id' => 'Bestellnummer',
      'order_total' => 'Bestellwert',
      'review_id' => 'Rezensions-ID',
      'rating' => 'Bewertung',
      'reference_type' => 'Vorgangstyp',
      'reference_id' => 'Vorgangs-ID',
      'balance' => 'Saldo nach Vorgang',
    ],
    'beneficiaries' => [
      'actor' => 'Teilnehmer',
      'upline' => 'Upline',
    ],
  ],

  'withdrawal' => [
    'approved_subject' => 'Auszahlungsantrag Nr. :id genehmigt',
    'approved_title' => 'Auszahlungsantrag Nr. :id genehmigt',
    'paid_subject' => 'Auszahlungsantrag Nr. :id ausgezahlt',
    'paid_title' => 'Auszahlungsantrag Nr. :id ausgezahlt',
    'approved_intro' => 'Wir haben Ihren Antrag Nr. :id genehmigt. Die Auszahlung wird in Kürze vorbereitet.',
    'paid_intro' => 'Ihr Antrag Nr. :id wurde ausgezahlt. Danke für Ihr Vertrauen.',
    'details_title' => 'Angaben zur Auszahlung',
    'button' => 'Zum Dashboard',
    'labels' => [
      'amount' => 'Auszahlungsbetrag',
      'wallet_amount' => 'Vom Wallet abgebucht',
      'status' => 'Status',
      'method' => 'Auszahlungsmethode',
      'approved_at' => 'Genehmigt am',
      'paid_at' => 'Ausgezahlt am',
      'requested_at' => 'Erstellt am',
      'fx_rate' => 'Wechselkurs',
    ],
    'statuses' => [
      'pending' => 'In Bearbeitung',
      'approved' => 'Genehmigt',
      'rejected' => 'Abgelehnt',
      'paid' => 'Ausgezahlt',
    ],
  ],

  'verify_email' => [
    'subject' => 'E-Mail-Adresse bestätigen',
    'greeting' => 'Willkommen!',
    'intro' => 'Bitte klicken Sie auf die untenstehende Schaltfläche, um Ihre E-Mail-Adresse zu bestätigen.',
    'button' => 'E-Mail bestätigen',
    'outro' => 'Falls Sie dieses Konto nicht erstellt haben, ignorieren Sie diese E-Mail bitte.',
  ],
];
