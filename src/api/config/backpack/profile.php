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
        ],
    ],
];
