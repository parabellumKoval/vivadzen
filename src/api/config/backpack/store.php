<?php

return [
    'currency' => [
      'value' => 'грн.',
      'symbol' => 'UAH',
    ],
    
    // CATALOG
    'per_page' => 12,

    // GUARD
    'auth_guard' => null,
    
    // USER
    'user_model' => 'Backpack\Profile\app\Models\Profile',

    // REVIEW
    'review_model' => 'Backpack\Reviews\app\Models\Review',
    'enable_reviews_in_product_crud' => true,

    // ORDER
    'order_model' => 'Backpack\Store\app\Models\Order',
    'enable_orders_in_product_crud' => true,
    
    'order' => [

      'enable_bonus' => false,

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
          'rules' => 'array:method,status',
          'store_in' => 'info',
          'method' => [
            'rules' => 'required|in:online,cash'
          ]
        ],
        
        'delivery' => [
          'rules' => 'array:settlement,settlementRef,street,streetRef,area,region,type,house,room,zip,method,warehouse,warehouseRef',
          'store_in' => 'info',
          'method' => [
            'rules' => 'required|in:address,warehouse,pickup'
          ],
          'warehouse' => [
            'rules' => 'required_if:delivery.method,warehouse|nullable|string|min:1|max:500'
          ],
          'warehouseRef' => [
            'rules' => 'nullable|string|min:1|max:500'
          ],
          'settlement' => [
            'rules' => 'required_if:delivery.method,address,warehouse|nullable|string|min:2|max:500'
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
            'rules' => 'required_if:delivery.method,address|nullable|string|min:2|max:255'
          ],
          'streetRef' => [
            'rules' => 'nullable|string|min:2|max:255'
          ],
          'house' => [
            'rules' => 'required_if:delivery.method,address|nullable|string|min:1|max:50'
          ],
          'room' => [
            'rules' => 'nullable|string|min:1|max:50'
          ],
          'zip' => [
            'rules' => 'required_if:delivery.method,address|nullable|string|min:5|max:255'
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
        
        'bonusesUsed' => [
          'rules' => 'nullable|numeric',
          'store_in' => 'info'
        ],
        
        'promocode' => [
          'rules' => 'nullable',
          'store_in' => 'info'
        ],
  
        'user' => [
          'rules' => 'array:firstname,lastname,phone,email',
          'store_in' => 'info',
          'firstname' => [
            'rules' => 'required_if:provider,data|string|min:2|max:150'
          ],
          'lastname' => [
            'rules' => 'nullable|string|min:2|max:150'
          ],
          'phone' => [
            'rules' => 'required_if:provider,data|string|min:2|max:80'
          ],
          'email' => [
            'rules' => 'nullable|email|min:2|max:150'
          ],
        ]
      ]
    ],

    // CATEGORIES
    'category' => [
      'class' => 'App\Models\Category',

      'depth_level' => 3,

      'per_page' => 12,

      'image' => [
        'enable' => true,
        'base_path' => 'https://djini-v2.b-cdn.net/categories/',
      ],

      'resource' => [
        'tiny' => 'App\Http\Resources\CategoryTinyResource',

        'small' => 'App\Http\Resources\CategorySmallResource',

        'large' => 'App\Http\Resources\CategoryLargeResource',
      ]
    ],

    // PROPUCT
    'product' => [
      'class' => 'App\Models\Product',
      'class_admin' => 'App\Models\Admin\Product',

      'seo' => [
        'enable' => true
      ],

      'image' => [
        'enable' => true,
        'base_path' => 'https://djini-v2.b-cdn.net/products/',
      ],

      'code' => [
        'enable' => true
      ],

      'price' => [
        'enable' => true
      ],
      
      'old_price' => [
        'enable' => true
      ],
      
      'modifications' => [
        'enable' => true
      ],

      'in_stock' => [
        'enable' => true
      ],


      'resource' => [
        // PRODUCT -> resources
        'tiny' => 'App\Http\Resources\ProductTinyResource',
        
        // Small product resource used for catalog pages (index route)
        'small' => 'App\Http\Resources\ProductSmallResource',
        'medium' => 'App\Http\Resources\ProductMediumResource',
        
        // Large product resource used for product page (show route)
        'large' => 'App\Http\Resources\ProductLargeResource',
    
        // Cart product resource used for order
        'cart' => 'Backpack\Store\app\Http\Resources\ProductCartResource',
      ]
    ],

    // ATTRIBUTES
    'attribute' => [
      'enable' => true,

      // Is pivot values translatable
      'translatable_value' => false,

      'enable_icon' => false,

      'resource' => [

        'product' => 'Backpack\Store\app\Http\Resources\AttributeProductResource',

        'large' => 'Backpack\Store\app\Http\Resources\AttributeLargeResource',

        'small' => 'Backpack\Store\app\Http\Resources\AttributeSmallResource'
      ]
    ],


    // PROMOCODE
    'promocodes' => [
      'enable' => true,

      'resource' => [

        'large' => 'Backpack\Store\app\Http\Resources\PromocodeLargeResource',

        'small' => 'Backpack\Store\app\Http\Resources\PromocodeSmallResource'
      ],

    ],
    

    // BRAND
    'brands' => [
      'class' => 'App\Models\Brand',

      'enable' => true,

      'image' => [
        'enable' => true,
        'base_path' => 'https://djini-v2.b-cdn.net/brands/',
      ],

      'resource' => [
        'large' => 'Backpack\Store\app\Http\Resources\BrandLargeResource',
        'small' => 'Backpack\Store\app\Http\Resources\BrandSmallResource'
      ],

      'alpha_groups' => [
        'patterns' => [
          '/[А-Яа-яЁё]/u',
          '/[0-9]/u',
          '/[A-Za-z]/u',
        ]
      ]
    ],

    // SUPPLIER
    'supplier' => [
      'enable' => true,
      'class' => 'Backpack\Store\app\Models\Supplier',
      'sp_class' => 'Backpack\Store\app\Models\SupplierProduct',
    ],

    // XML SOURCE
    'source' => [
      'enable' => true,
      'class' => 'Backpack\Store\app\Models\Source',
      'admin_class' => 'Backpack\Store\app\Models\Admin\Source',
      'upload_class' => 'Backpack\Store\app\Models\UploadHistory',
      'test' => [
        'enable' => env('STORE_SOURCE_TEST', false),
        'items' => env('STORE_SOURCE_TEST_ITEMS', 1)
      ]
    ]
];
