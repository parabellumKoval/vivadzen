<?php

namespace App\Providers;

use App\Listeners\ResyncProductOnReviewPublished;
use App\Listeners\SendReferralSponsorNotification;
use App\Listeners\SendRewardLedgerEntryNotification;
use App\Listeners\SendWithdrawalApprovedMail;
use App\Listeners\SendWithdrawalPaidMail;
use App\Observers\FeedbackObserver;
use App\Observers\OrderObserver;
use Backpack\Profile\app\Events\ReferralAttached;
use Backpack\Profile\app\Events\RewardLedgerEntryCreated;
use Backpack\Profile\app\Events\WithdrawalApproved;
use Backpack\Profile\app\Events\WithdrawalPaid;
use Backpack\Reviews\app\Events\ReviewPublished;
use Backpack\Feedback\app\Models\Feedback;
use Backpack\Store\app\Models\Order as StoreOrder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

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
     * Register any events for your application.
     */
    public function register(): void
    {
        // Prevent duplicate email verification listeners by clearing any defaults
        // before parent::register is called
        $this->ensureSingleEmailVerificationListener();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        StoreOrder::observe(OrderObserver::class);
        Feedback::observe(FeedbackObserver::class);
    }

    /**
     * Ensure only a single email verification listener is registered.
     * Prevents duplicate emails when using multiple EventServiceProviders.
     */
    protected function ensureSingleEmailVerificationListener(): void
    {
        // Remove all existing listeners for the Registered event
        Event::forget(Registered::class);
        
        // Register only our single listener
        Event::listen(Registered::class, SendEmailVerificationNotification::class);
    }
}
