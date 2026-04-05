<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\DeliveryReportRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class DeliveryReportCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(\App\Models\DeliveryReport::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/delivery-report');
        CRUD::setEntityNameStrings('отчет доставки', 'отчеты доставки');
        CRUD::orderBy('id', 'desc');
    }

    protected function setupListOperation(): void
    {
        CRUD::addFilter([
            'name' => 'provider',
            'type' => 'select2',
            'label' => 'Провайдер',
        ], [
            'messenger' => 'Messenger.cz',
        ], function ($value): void {
            CRUD::addClause('where', 'provider', $value);
        });

        CRUD::addFilter([
            'name' => 'order_found',
            'type' => 'dropdown',
            'label' => 'Заказ найден',
        ], [
            1 => 'Да',
            0 => 'Нет',
        ], function ($value): void {
            CRUD::addClause('where', 'order_found', (int) $value);
        });

        $this->addBaseColumns();
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(DeliveryReportRequest::class);

        CRUD::addField([
            'name' => 'provider',
            'label' => 'Провайдер',
            'type' => 'select_from_array',
            'options' => [
                'messenger' => 'Messenger.cz',
            ],
            'default' => 'messenger',
            'allows_null' => false,
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'order_number',
            'label' => 'Номер заказа',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'id_card_type',
            'label' => 'Тип документа',
            'type' => 'select_from_array',
            'options' => [
                'op' => 'op',
                'passport' => 'passport',
                'residence' => 'residence',
            ],
            'allows_null' => false,
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'recipient_fullname',
            'label' => 'Получатель',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'recipient_actual_fullname',
            'label' => 'Фактический получатель',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'id_card_number',
            'label' => 'Номер документа',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'handover_datetime',
            'label' => 'Дата и время вручения',
            'type' => 'datetime_picker',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'sender_fullname',
            'label' => 'Курьер',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);

        CRUD::addField([
            'name' => 'handover_place',
            'label' => 'Место вручения',
            'type' => 'text',
        ]);

        CRUD::addField([
            'name' => 'is_test',
            'label' => 'Тестовый callback',
            'type' => 'checkbox',
            'default' => false,
        ]);

        CRUD::addField([
            'name' => 'customer_signature',
            'label' => 'Подпись клиента',
            'type' => 'textarea',
            'attributes' => ['rows' => 4],
        ]);

        CRUD::addField([
            'name' => 'seller_signature',
            'label' => 'Подпись курьера',
            'type' => 'textarea',
            'attributes' => ['rows' => 4],
        ]);

        CRUD::addField([
            'name' => 'payload',
            'label' => 'Payload JSON',
            'type' => 'textarea',
            'attributes' => ['rows' => 8],
            'hint' => 'Необязательно. Можно вставить JSON объекта callback.',
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation(): void
    {
        $this->addBaseColumns();

        CRUD::addColumn([
            'name' => 'handover_place',
            'label' => 'Место вручения',
        ]);

        CRUD::addColumn([
            'name' => 'id_card_number',
            'label' => 'Номер документа',
        ]);

        CRUD::addColumn([
            'name' => 'id_card_type',
            'label' => 'Тип документа',
        ]);

        CRUD::addColumn([
            'name' => 'processed_at',
            'label' => 'Обработан',
            'type' => 'datetime',
        ]);

        CRUD::addColumn([
            'name' => 'customer_signature_preview',
            'label' => 'Подпись клиента',
            'type' => 'model_function',
            'function_name' => 'customerSignaturePreview',
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'seller_signature_preview',
            'label' => 'Подпись курьера',
            'type' => 'model_function',
            'function_name' => 'sellerSignaturePreview',
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'payload_preview',
            'label' => 'Payload',
            'type' => 'model_function',
            'function_name' => 'payloadPreview',
            'escaped' => false,
        ]);
    }

    protected function addBaseColumns(): void
    {
        CRUD::addColumn([
            'name' => 'id',
            'label' => '#',
        ]);

        CRUD::addColumn([
            'name' => 'provider',
            'label' => 'Провайдер',
        ]);

        CRUD::addColumn([
            'name' => 'order_number',
            'label' => 'Номер заказа',
        ]);

        CRUD::addColumn([
            'name' => 'order_link',
            'label' => 'Заказ',
            'type' => 'model_function',
            'function_name' => 'orderLink',
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'recipient_fullname',
            'label' => 'Получатель',
        ]);

        CRUD::addColumn([
            'name' => 'handover_datetime',
            'label' => 'Дата вручения',
            'type' => 'datetime',
        ]);

        CRUD::addColumn([
            'name' => 'sender_fullname',
            'label' => 'Курьер',
        ]);

        CRUD::addColumn([
            'name' => 'is_test',
            'label' => 'Тест',
            'type' => 'boolean',
        ]);

        CRUD::addColumn([
            'name' => 'order_found',
            'label' => 'Заказ найден',
            'type' => 'boolean',
        ]);

        CRUD::addColumn([
            'name' => 'delivery_status_applied',
            'label' => 'Доставка обновлена',
            'type' => 'boolean',
        ]);

        CRUD::addColumn([
            'name' => 'pay_status_applied',
            'label' => 'Оплата обновлена',
            'type' => 'boolean',
        ]);

        CRUD::addColumn([
            'name' => 'order_status_applied',
            'label' => 'Статус заказа обновлен',
            'type' => 'boolean',
        ]);

        CRUD::addColumn([
            'name' => 'created_at',
            'label' => 'Получен',
            'type' => 'datetime',
        ]);
    }
}
