<?php

return [
  'model' => 'Backpack\Store\app\Models\Order',

  'model_admin' => 'Backpack\Store\app\Models\Order',

  'enable_bonus' => false,

  'bonus' => [
    'enabled' => env('ORDER_BONUS_ENABLED', false),
    // 'service' => \Backpack\Store\app\Services\Bonus\NullBonusService::class,
    // 'service' => \Backpack\Profile\app\Services\BonusAccountService::class
    'service' => \Backpack\Store\app\Services\Bonus\ProfileBonusServiceAdapter::class
  ],

  'per_page' => 12,

  'resource' => [
    'large' => 'Backpack\Store\app\Http\Resources\OrderLargeResource',
  ],

  // Common order statuses
  'status' => [
    'default' => 'new',
    'values' => ['new', 'canceled', 'failed', 'completed']
  ],
  // Payment statuses
  'pay_status' => [
    'default' => 'waiting',
    'values' => ['waiting', 'failed', 'paied']
  ],
  // Delivery statuses 
  'delivery_status' => [
    'default' => 'waiting',
    'values' => ['waiting', 'sent', 'failed', 'delivered', 'pickedup']
  ],
  // Validation fields
  'fields' => [
    'orderable_id' => [
      'rules' => 'nullable|uuid',
    ],

    'orderable_type' => [
      'rules' => 'nullable|max:255',
    ],

    'provider' => [
      'rules' => 'required|in:auth,data,outer',
      'store_in' => 'info'
    ],

    'payment' => [
      'rules' => 'array:method,settlement,street,house,room,zip',
      'store_in' => 'info',
      'method' => [
        'rules' => 'required|in:zasilkovna_cod,novaposhta_cod,default_cash,liqpay_online,card_online,bank_transfer'
      ],
      'settlement' => [
        'rules' => 'required_if:payment.method,bank_transfer|required_unless:delivery.method,novaposhta_warehouse,packeta_warehouse,default_pickup|nullable|string|min:2|max:500'
      ],
      'street' => [
        'rules' => 'required_if:payment.method,bank_transfer|required_unless:delivery.method,novaposhta_warehouse,packeta_warehouse,default_pickup|nullable|string|min:2|max:255'
      ],
      'house' => [
        'rules' => 'required_if:payment.method,bank_transfer|required_unless:delivery.method,novaposhta_warehouse,packeta_warehouse,default_pickup|nullable|string|min:1|max:50'
      ],
      'room' => [
        'rules' => 'required_if:payment.method,bank_transfer|required_unless:delivery.method,novaposhta_warehouse,packeta_warehouse,default_pickup|nullable|string|min:1|max:50'
      ],
      'zip' => [
        'rules' => 'required_if:payment.method,bank_transfer|required_unless:delivery.method,novaposhta_warehouse,packeta_warehouse,default_pickup|nullable|string|min:5|max:255'
      ]
    ],

    'delivery' => [
      'rules' => 'array:settlement,settlementRef,street,streetRef,area,region,type,house,room,zip,method,warehouse,warehouseRef,price,priceCurrency',
      'store_in' => 'info',
      'method' => [
        'rules' => 'required|in:novaposhta_address,novaposhta_warehouse,packeta_address,packeta_warehouse,default_pickup'
      ],
      'warehouse' => [
        'rules' => 'required_if:delivery.method,novaposhta_warehouse,packeta_warehouse|nullable|string|min:1|max:500'
      ],
      'warehouseRef' => [
        'rules' => 'nullable|string|min:1|max:500'
      ],
      'settlement' => [
        'rules' => 'required_if:delivery.method,novaposhta_address,novaposhta_warehouse,packeta_address,packeta_warehouse|nullable|string|min:2|max:500'
      ],
      'settlementRef' => [
        'rules' => 'nullable|string|min:1|max:500'
      ],
      'area' => [
        'rules' => 'nullable|string|min:1|max:500'
      ],
      'region' => [
        'rules' => 'nullable|string|min:1|max:500'
      ],
      'type' => [
        'rules' => 'nullable|string|min:1|max:500'
      ],
      'street' => [
        'rules' => 'required_if:delivery.method,novaposhta_address,packeta_address|nullable|string|min:2|max:255'
      ],
      'streetRef' => [
        'rules' => 'nullable|string|min:2|max:255'
      ],
      'house' => [
        'rules' => 'required_if:delivery.method,novaposhta_address|nullable|string|min:1|max:50'
      ],
      'room' => [
        'rules' => 'nullable|string|min:1|max:50'
      ],
      'zip' => [
        'rules' => 'required_if:delivery.method,novaposhta_address,packeta_address|nullable|string|min:5|max:255'
      ]
    ],
    
    'comment' => [
      'rules' => 'nullable|string|min:1|max:1000',
      'store_in' => 'info'
    ],
    
    'products' => [
      'rules' => 'required|array',
      'hidden' => true,
    ],

    'currency_code' => [
      'rules' => 'nullable|string|size:3'
    ],

    // 'subtotal' => [
    //   'rules' => 'nullable|numeric|min:0',
    //   'hidden' => true,
    // ],

    // 'discount_total' => [
    //   'rules' => 'nullable|numeric|min:0',
    //   'hidden' => true,
    // ],

    // 'shipping_total' => [
    //   'rules' => 'nullable|numeric|min:0',
    // ],

    // 'tax_total' => [
    //   'rules' => 'nullable|numeric|min:0',
    // ],

    // 'grand_total' => [
    //   'rules' => 'nullable|numeric|min:0',
    //   'hidden' => true,
    // ],

    // 'shipping_country_code' => [
    //   'rules' => 'nullable|string|size:2'
    // ],

    // 'billing_country_code' => [
    //   'rules' => 'nullable|string|size:2'
    // ],
    
    // 'bonusesUsed' => [
    //   'rules' => 'nullable|numeric',
    //   'store_in' => 'info'
    // ],

    'bonus' => [
      'rules' => 'nullable|numeric|min:0',
      'hidden' => true,
    ],

    'bonusInFiat' => [
      'rules' => 'nullable|numeric|min:0',
      'hidden' => true,
    ],
    
    'promocode' => [
      'rules' => 'nullable',
      'store_in' => 'info'
    ],

    'user' => [
      'rules' => 'array:first_name,last_name,phone,email',
      'store_in' => 'info',
      'first_name' => [
        'rules' => 'required_if:payment.method,bank_transfer|required_if:delivery.method,packeta_warehouse,packeta_address|nullable|string|min:2|max:150'
      ],
      'last_name' => [
        'rules' => 'required_if:payment.method,bank_transfer|required_if:delivery.method,packeta_warehouse,packeta_address|nullable|string|min:2|max:150'
      ],
      'phone' => [
        'rules' => 'required|string|min:2|max:80'
      ],
      'email' => [
        'rules' => 'nullable|email|min:2|max:150'
      ],
    ]
  ]
];
