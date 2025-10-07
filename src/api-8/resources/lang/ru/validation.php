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

    'accepted' => 'Поле :attribute должно быть принято.',
    'accepted_if' => 'Поле :attribute должно быть принято, когда :other равно :value.',
    'active_url' => 'Поле :attribute не является корректным URL.',
    'after' => 'Поле :attribute должно быть датой после :date.',
    'after_or_equal' => 'Поле :attribute должно быть датой после или равной :date.',
    'alpha' => 'Поле :attribute должно содержать только буквы.',
    'alpha_dash' => 'Поле :attribute должно содержать только буквы, цифры, дефисы и подчеркивания.',
    'alpha_num' => 'Поле :attribute должно содержать только буквы и цифры.',
    'array' => 'Поле :attribute должно быть массивом.',
    'before' => 'Поле :attribute должно быть датой до :date.',
    'before_or_equal' => 'Поле :attribute должно быть датой до или равной :date.',
    'between' => [
      'numeric' => 'Поле :attribute должно быть между :min и :max.',
      'file' => 'Поле :attribute должно быть между :min и :max килобайт.',
      'string' => 'Поле :attribute должно быть между :min и :max символов.',
      'array' => 'Поле :attribute должно содержать от :min до :max элементов.',
    ],
    'boolean' => 'Поле :attribute должно быть true или false.',
    'confirmed' => 'Поле :attribute не совпадает с подтверждением.',
    'current_password' => 'Неверный пароль.',
    'date' => 'Поле :attribute не является корректной датой.',
    'date_equals' => 'Поле :attribute должно быть датой, равной :date.',
    'date_format' => 'Поле :attribute не соответствует формату :format.',
    'declined' => 'Поле :attribute должно быть отклонено.',
    'declined_if' => 'Поле :attribute должно быть отклонено, когда :other равно :value.',
    'different' => 'Поле :attribute и :other должны быть разными.',
    'digits' => 'Поле :attribute должно быть :digits цифр.',
    'digits_between' => 'Поле :attribute должно быть от :min до :max цифр.',
    'dimensions' => 'Поле :attribute имеет недопустимые размеры изображения.',
    'distinct' => 'Поле :attribute содержит повторяющееся значение.',
    'email' => 'Поле :attribute должно быть корректным адресом электронной почты.',
    'ends_with' => 'Поле :attribute должно заканчиваться одним из следующих значений: :values.',
    'enum' => 'Выбранный :attribute недопустим.',
    'exists' => 'Выбранный :attribute недопустим.',
    'file' => ':attribute должен быть файлом.',
    'filled' => 'Поле :attribute должно содержать значение.',
    'gt' => [
      'numeric' => ':attribute должен быть больше чем :value.',
      'file' => ':attribute должен быть больше чем :value килобайт.',
      'string' => ':attribute должен содержать больше чем :value символов.',
      'array' => ':attribute должен содержать больше чем :value элементов.',
    ],
    'gte' => [
      'numeric' => ':attribute должен быть больше или равен :value.',
      'file' => ':attribute должен быть больше или равен :value килобайт.',
      'string' => ':attribute должен содержать больше или равно :value символов.',
      'array' => ':attribute должен содержать :value элементов или больше.',
    ],
    'image' => ':attribute должен быть изображением.',
    'in' => 'Выбранный :attribute недопустим.',
    'in_array' => 'Поле :attribute не существует в :other.',
    'integer' => ':attribute должен быть целым числом.',
    'ip' => ':attribute должен быть действительным IP-адресом.',
    'ipv4' => ':attribute должен быть действительным IPv4-адресом.',
    'ipv6' => ':attribute должен быть действительным IPv6-адресом.',
    'json' => ':attribute должен быть допустимой JSON-строкой.',
    'lt' => [
      'numeric' => 'Значение поля :attribute должно быть меньше :value.',
      'file' => 'Файл :attribute должен быть меньше :value килобайт.',
      'string' => 'Длина поля :attribute должна быть меньше :value символов.',
      'array' => 'Массив :attribute должен содержать меньше :value элементов.',
    ],
    'lte' => [
      'numeric' => 'Значение поля :attribute должно быть меньше или равно :value.',
      'file' => 'Файл :attribute должен быть меньше или равен :value килобайт.',
      'string' => 'Длина поля :attribute должна быть меньше или равна :value символов.',
      'array' => 'Массив :attribute не должен содержать более :value элементов.',
    ],
    'mac_address' => 'Значение поля :attribute должно быть корректным MAC-адресом.',
    'max' => [
      'numeric' => 'Значение поля :attribute не должно превышать :max.',
      'file' => 'Файл :attribute не должен превышать :max килобайт.',
      'string' => 'Длина поля :attribute не должна превышать :max символов.',
      'array' => 'Массив :attribute не должен содержать более :max элементов.',
    ],
    'mimes' => 'Файл :attribute должен быть типа: :values.',
    'mimetypes' => 'Файл :attribute должен быть типа: :values.',
    'min' => [
      'numeric' => 'Значение поля :attribute должно быть не менее :min.',
      'file' => 'Файл :attribute должен быть не менее :min килобайт.',
      'string' => 'Длина поля :attribute должна быть не менее :min символов.',
      'array' => 'Массив :attribute должен содержать не менее :min элементов.',
    ],
    'multiple_of' => 'Значение поля :attribute должно быть кратным :value.',
    'not_in' => 'Выбранное значение поля :attribute недопустимо.',
    'not_regex' => 'Недопустимый формат поля :attribute.',
    'numeric' => 'Значение поля :attribute должно быть числом.',
    'password' => 'Неправильный пароль.',
    'present' => 'Поле :attribute должно присутствовать.',
    'prohibited' => 'Поле :attribute запрещено.',
    'prohibited_if' => 'Поле :attribute запрещено, когда :other имеет значение :value.',
    'prohibited_unless' => 'Поле :attribute запрещено, если :other не принадлежит к :values.',
    'prohibits' => 'Поле :attribute запрещает наличие поля :other.',
    'regex' => 'Недопустимый формат поля :attribute.',
    'required' => 'Поле :attribute обязательно для заполнения.',
    'required_array_keys' => 'Поле :attribute должно содержать значения для ключей: :values.',
    'required_if' => 'Поле :attribute обязательно, когда :other имеет значение :value.',
    'required_unless' => 'Поле :attribute обязательно, если :other не принадлежит к :values.',
    'required_with' => 'Поле :attribute обязательно, когда присутствует :values.',
    'required_with_all' => 'Поле :attribute обязательно, когда присутствуют все значения :values.',
    'required_without' => 'Поле :attribute обязательно, когда отсутствует :values.',
    'required_without_all' => 'Поле :attribute обязательно, когда отсутствуют все значения :values.',
    'same' => 'Значение поля :attribute должно совпадать с :other.',
    'size' => [
      'numeric' => ':attribute должно быть :size.',
      'file' => ':attribute должно быть :size килобайт.',
      'string' => ':attribute должно быть :size символов.',
      'array' => ':attribute должно содержать :size элементов.',
    ],
    'starts_with' => ':attribute должно начинаться с одного из следующих значений: :values.',
    'string' => ':attribute должно быть строкой.',
    'timezone' => ':attribute должно быть допустимым часовым поясом.',
    'unique' => ':attribute уже занят.',
    'uploaded' => 'Не удалось загрузить :attribute.',
    'url' => ':attribute должно быть допустимым URL-адресом.',
    'uuid' => ':attribute должно быть допустимым UUID.',

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
      'provider' => '<b>Получатель</b>',
      'user' => [
        'firstname' => '<b>Имя</b>',
        'lastname' => '<b>Фамилия</b>',
        'email' => '<b>Email</b>',
        'phone' => '<b>Телефон</b>'
      ],
      'delivery' => [
        'method' => '<b>Метод доставки</b>',
        'settlement' => '<b>Населенный пункт</b>',
        'warehouse' => '<b>Номер отделения</b>',
        'street' => '<b>Улица</b>',
        'house' => '<b>Номер дома</b>',
        'room' => '<b>Квартира</b>',
        'zip' => '<b>Индекс</b>'
      ],
      'payment' => [
        'method' => '<b>Метод оплаты</b>'
      ],
      'comment' => '<b>Комментарий</b>'
    ],

    'values' => [
      'provider' => [
        'data' => '<b>Гость</b>'
      ],
      'delivery' => [
        'method' => [
          'address' => '<b>Курьер</b>',
          'warehouse' => '<b>Отделение почты</b>',
          'pickup' => '<b>Самовывоз</b>'
        ]
      ]
    ]

];
