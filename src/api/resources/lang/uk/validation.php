<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Поле :attribute повинно бути прийнято.',
    'accepted_if' => 'Поле :attribute повинно бути прийнято, якщо :other дорівнює :value.',
    'active_url' => 'Поле :attribute має невірний формат URL.',
    'after' => 'Поле :attribute повинно бути датою після :date.',
    'after_or_equal' => 'Поле :attribute повинно бути датою після або рівною :date.',
    'alpha' => 'Поле :attribute повинно містити лише літери.',
    'alpha_dash' => 'Поле :attribute повинно містити лише літери, цифри, дефіси та символи підкреслення.',
    'alpha_num' => 'Поле :attribute повинно містити лише літери та цифри.',
    'array' => 'Поле :attribute повинно бути масивом.',
    'before' => 'Поле :attribute повинно бути датою до :date.',
    'before_or_equal' => 'Поле :attribute повинно бути датою до або рівною :date.',
    'between' => [
      'numeric' => 'Поле :attribute повинно бути між :min та :max.',
      'file' => 'Поле :attribute повинно бути між :min та :max кілобайт.',
      'string' => 'Поле :attribute повинно містити від :min до :max символів.',
      'array' => 'Поле :attribute повинно містити від :min до :max елементів.',
    ],
    'boolean' => 'Поле :attribute повинне бути true або false.',
    'confirmed' => 'Підтвердження :attribute не збігається.',
    'current_password' => 'Неправильний пароль.',
    'date' => 'Поле :attribute не є дійсною датою.',
    'date_equals' => 'Поле :attribute повинне бути датою, рівною :date.',
    'date_format' => 'Поле :attribute не відповідає формату :format.',
    'declined' => 'Поле :attribute повинне бути declined.',
    'declined_if' => 'Поле :attribute повинне бути declined, коли :other є :value.',
    'different' => 'Поля :attribute і :other повинні бути різними.',
    'digits' => 'Поле :attribute повинне містити :digits цифр.',
    'digits_between' => 'Поле :attribute повинне містити від :min до :max цифр.',
    'dimensions' => 'У :attribute недійсні розміри зображення.',
    'distinct' => 'Поле :attribute має дубльоване значення.',
    'email' => 'Поле :attribute повинне бути дійсною електронною адресою.',
    'ends_with' => 'Поле :attribute повинне закінчуватися одним з наступних значень: :values.',
    'enum' => 'Обраний :attribute є недійсним.',
    'exists' => 'Обраний :attribute є недійсним.',
    'file' => 'Поле :attribute повинне бути файлом.',
    'filled' => 'Поле :attribute повинне мати значення.',
    'gt' => [
      'numeric' => 'Поле :attribute повинно бути більше ніж :value.',
      'file' => 'Поле :attribute повинно бути більше ніж :value кілобайт.',
      'string' => 'Поле :attribute повинно мати більше ніж :value символів.',
      'array' => 'Поле :attribute повинно мати більше ніж :value елементів.',
    ],
    'gte' => [
      'numeric' => 'Поле :attribute повинно бути більше або дорівнювати :value.',
      'file' => 'Поле :attribute повинно бути більше або дорівнювати :value кілобайт.',
      'string' => 'Поле :attribute повинно мати більше або дорівнювати :value символів.',
      'array' => 'Поле :attribute повинно мати :value елементів або більше.',
    ],
    'image' => 'Поле :attribute повинно бути зображенням.',
    'in' => 'Вибране значення для поля :attribute є недійсним.',
    'in_array' => 'Поле :attribute не існує в :other.',
    'integer' => 'Поле :attribute повинно бути цілим числом.',
    'ip' => 'Поле :attribute повинно бути дійсною IP-адресою.',
    'ipv4' => 'Поле :attribute повинно бути дійсною IPv4-адресою.',
    'ipv6' => 'Поле :attribute повинно бути дійсною IPv6-адресою.',
    'json' => 'Поле :attribute повинно бути дійсним JSON рядком.',
    'lt' => [
      'numeric' => 'Поле :attribute повинно бути меншим за :value.',
      'file' => 'Файл :attribute повинен бути меншим за :value кілобайтів.',
      'string' => 'Поле :attribute повинно бути меншим за :value символів.',
      'array' => 'Поле :attribute повинно містити менше ніж :value елементів.',
    ],
    'lte' => [
      'numeric' => 'Поле :attribute повинно бути меншим або дорівнювати :value.',
      'file' => 'Файл :attribute повинен бути меншим або дорівнювати :value кілобайтів.',
      'string' => 'Поле :attribute повинно бути меншим або дорівнювати :value символів.',
      'array' => 'Поле :attribute не повинно містити більше ніж :value елементів.',
    ],
    'mac_address' => 'Поле :attribute повинно бути дійсною MAC-адресою.',
    'max' => [
      'numeric' => 'Поле :attribute не повинно бути більшим ніж :max.',
      'file' => 'Файл :attribute не повинен бути більшим ніж :max кілобайтів.',
      'string' => 'Поле :attribute не повинно бути більшим ніж :max символів.',
      'array' => 'Поле :attribute не повинно містити більше ніж :max елементів.',
    ],
    'mimes' => 'Поле :attribute має бути файлом одного з типів: :values.',
    'mimetypes' => 'Поле :attribute має бути файлом одного з типів: :values.',
    'min' => [
      'numeric' => 'Поле :attribute повинно бути не менше ніж :min.',
      'file' => 'Файл :attribute повинен бути не менше ніж :min кілобайтів.',
      'string' => 'Поле :attribute повинно містити не менше ніж :min символів.',
      'array' => 'Поле :attribute повинно містити принаймні :min елементів.',
    ],
    'multiple_of' => 'Значення поля :attribute має бути кратним :value.',
    'not_in' => 'Вибране значення для поля :attribute недійсне.',
    'not_regex' => 'Формат поля :attribute неправильний.',
    'numeric' => 'Поле :attribute має бути числом.',
    'password' => 'Неправильний пароль.',
    'present' => 'Поле :attribute повинно бути присутнє.',
    'prohibited' => 'Поле :attribute заборонено.',
    'prohibited_if' => 'Поле :attribute заборонено, коли :other має значення :value.',
    'prohibited_unless' => 'Поле :attribute заборонено, якщо :other не входить до :values.',
    'prohibits' => 'Поле :attribute забороняє наявність :other.',
    'regex' => 'Формат поля :attribute неправильний.',
    'required' => "Поле :attribute обов'язкове.",
    'required_array_keys' => 'Для поля :attribute повинні бути вказані значення для наступних ключів: :values.',
    'required_if' => "Поле :attribute обов'язкове, коли :other має значення :value.",
    'required_unless' => "Поле :attribute обов'язкове, якщо :other не входить до :values.",
    'required_with' => "Поле :attribute обов'язкове, коли присутнє :values.",
    'required_with_all' => "Поле :attribute обов'язкове, коли присутні всі :values.",
    'required_without' => "Поле :attribute обов'язкове, коли відсутнє :values.",
    'required_without_all' => "Поле :attribute обов'язкове, коли відсутні всі :values.",
    'same' => 'Значення полів :attribute та :other мають співпадати.',
    'size' => [
      'numeric' => 'Поле :attribute повинно бути :size.',
      'file' => 'Поле :attribute повинно мати розмір :size кілобайтів.',
      'string' => 'Поле :attribute повинно містити :size символів.',
      'array' => 'Поле :attribute повинно містити :size елементів.',
    ],
    'starts_with' => 'Поле :attribute повинно починатися з одного з наступних значень: :values.',
    'string' => 'Поле :attribute повинно бути рядком.',
    'timezone' => 'Поле :attribute повинно бути коректною часовою зоною.',
    'unique' => 'Значення поля :attribute вже зайняте.',
    'uploaded' => 'Не вдалося завантажити файл поля :attribute.',
    'url' => 'Поле :attribute повинно бути коректною URL-адресою.',
    'uuid' => 'Поле :attribute повинно бути коректним UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
      'provider' => '<b>Отримувач</b>',
      'user' => [
        'firstname' => "<b>Ім'я</b>",
        'lastname' => '<b>Прізвище</b>',
        'email' => '<b>Email</b>',
        'phone' => '<b>Телефон</b>'
      ],
      'delivery' => [
        'method' => '<b>Метод доставки</b>',
        'settlement' => '<b>Населений пункт</b>',
        'warehouse' => '<b>Номер відділення</b>',
        'street' => '<b>Вулиця</b>',
        'house' => '<b>Номер будинку</b>',
        'room' => '<b>Квартира</b>',
        'zip' => '<b>Індекс</b>'
      ],
      'payment' => [
        'method' => '<b>Метод оплати</b>'
      ],
      'comment' => '<b>Коментар</b>'
    ],
     
    'values' => [
      'provider' => [
        'data' => '<b>Гість</b>'
      ],
      'delivery' => [
        'method' => [
          'address' => "<b>Кур'єр</b>",
          'warehouse' => '<b>Відділення пошти</b>',
          'pickup' => '<b>Самовивіз</b>'
        ]
      ]
    ]

];
