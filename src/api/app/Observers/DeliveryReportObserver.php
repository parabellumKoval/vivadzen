<?php

namespace App\Observers;

use App\Models\DeliveryReport;
use App\Services\DeliveryReports\DeliveryReportOrderSyncService;

class DeliveryReportObserver
{
    private static bool $syncing = false;

    public function saved(DeliveryReport $report): void
    {
        if (self::$syncing) {
            return;
        }

        self::$syncing = true;

        try {
            app(DeliveryReportOrderSyncService::class)->sync($report);
        } finally {
            self::$syncing = false;
        }
    }
}
