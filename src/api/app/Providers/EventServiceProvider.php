<?php

namespace App\Providers;

use App\Listeners\ResyncProductOnReviewPublished;
use App\Listeners\SendReferralSponsorNotification;
use App\Listeners\SendRewardLedgerEntryNotification;
use App\Listeners\SendWithdrawalApprovedMail;
use App\Listeners\SendWithdrawalPaidMail;
use App\Observers\OrderObserver;
use Backpack\Profile\app\Events\ReferralAttached;
use Backpack\Profile\app\Events\RewardLedgerEntryCreated;
use Backpack\Profile\app\Events\WithdrawalApproved;
use Backpack\Profile\app\Events\WithdrawalPaid;
use Backpack\Reviews\app\Events\ReviewPublished;
use Backpack\Store\app\Models\Order as StoreOrder;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ReviewPublished::class => [
            ResyncProductOnReviewPublished::class,
        ],
        ReferralAttached::class => [
            SendReferralSponsorNotification::class,
        ],
        RewardLedgerEntryCreated::class => [
            SendRewardLedgerEntryNotification::class,
        ],
        WithdrawalApproved::class => [
            SendWithdrawalApprovedMail::class,
        ],
        WithdrawalPaid::class => [
            SendWithdrawalPaidMail::class,
        ],
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        StoreOrder::observe(OrderObserver::class);
    }
}
