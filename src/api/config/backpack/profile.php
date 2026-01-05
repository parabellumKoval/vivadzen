<?php

return [
    'role_fields' => [
        'bot' => [
            [
                'name' => 'character',
                'label' => 'Характер',
                'type' => 'textarea',
                'wrapper' => ['class' => 'form-group col-md-12'],
                'validation_rules' => ['nullable', 'string', 'max:255'],
            ],
            [
                'name' => 'gender',
                'label' => 'Пол',
                'type' => 'select_from_array',
                'options' => [
                    'male' => 'Мужской',
                    'female' => 'Женский',
                ],
                'allows_null' => true,
                'wrapper' => ['class' => 'form-group col-md-4'],
                'validation_rules' => ['nullable', 'string', 'max:32'],
            ],
            [
                'name' => 'age',
                'label' => 'Возраст',
                'type' => 'number',
                'attributes' => [
                    'min' => 0,
                    'max' => 150,
                ],
                'wrapper' => ['class' => 'form-group col-md-4'],
                'validation_rules' => ['nullable', 'integer', 'min:0', 'max:150'],
            ],
            [
                'name' => 'literacy_level',
                'label' => 'Уровень грамотности (0-10)',
                'type' => 'number',
                'attributes' => [
                    'min' => 0,
                    'max' => 10,
                    'step' => 1,
                ],
                'wrapper' => ['class' => 'form-group col-md-4'],
                'validation_rules' => ['nullable', 'integer', 'min:0', 'max:10'],
            ],
            [
                'name' => 'speech_style',
                'label' => 'Стиль речи',
                'type' => 'textarea',
                'attributes' => [
                    'rows' => 2,
                ],
                'wrapper' => ['class' => 'form-group col-md-12'],
                'validation_rules' => ['nullable', 'string', 'max:255'],
            ],
            [
                'name' => 'emoji_usage',
                'label' => 'Использование смайликов/эмоджи',
                'type' => 'textarea',
                'attributes' => [
                    'rows' => 2,
                ],
                'wrapper' => ['class' => 'form-group col-md-12'],
                'validation_rules' => ['nullable', 'string', 'max:255'],
            ],
            [
                'name' => 'punctuation_usage',
                'label' => 'Использование пунктуации и знаков',
                'type' => 'textarea',
                'attributes' => [
                    'rows' => 2,
                ],
                'wrapper' => ['class' => 'form-group col-md-12'],
                'validation_rules' => ['nullable', 'string', 'max:255'],
            ],
            [
                'name' => 'message_length',
                'label' => 'Длина сообщений',
                'type' => 'text',
                'wrapper' => ['class' => 'form-group col-md-6'],
                'validation_rules' => ['nullable', 'string', 'max:255'],
            ],
        ],
    ],
];
