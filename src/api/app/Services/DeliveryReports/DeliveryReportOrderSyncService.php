<?php

namespace App\Services\DeliveryReports;

use App\Models\DeliveryReport;

class DeliveryReportOrderSyncService
{
    public function sync(DeliveryReport $report): void
    {
        $order = $report->resolveOrder();

        $attributes = [
            'order_found' => $order !== null,
            'delivery_status_applied' => false,
            'pay_status_applied' => false,
            'order_status_applied' => false,
            'processed_at' => now(),
        ];

        if ($order) {
            if ($this->shouldApplyDeliveryStatus() && $order->delivery_status !== 'delivered') {
                $order->delivery_status = 'delivered';
                $attributes['delivery_status_applied'] = true;
            }

            if ($this->shouldApplyPayStatus() && $order->pay_status !== 'paied') {
                $order->pay_status = 'paied';
                $attributes['pay_status_applied'] = true;
            }

            if ($this->shouldApplyOrderStatus() && $order->status !== 'completed') {
                $order->status = 'completed';
                $attributes['order_status_applied'] = true;
            }

            if ($order->isDirty()) {
                $order->save();
            }
        }

        $report->forceFill($attributes);

        if ($report->isDirty(array_keys($attributes))) {
            $report->saveQuietly();
        }
    }

    protected function shouldApplyDeliveryStatus(): bool
    {
        return (bool) \Settings::get('shipping.messenger.reporting.apply_delivery_status', true);
    }

    protected function shouldApplyPayStatus(): bool
    {
        return (bool) \Settings::get('shipping.messenger.reporting.apply_pay_status', true);
    }

    protected function shouldApplyOrderStatus(): bool
    {
        return (bool) \Settings::get('shipping.messenger.reporting.apply_order_status', true);
    }
}
