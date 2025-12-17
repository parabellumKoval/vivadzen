<?php

namespace App\Observers;

use Illuminate\Support\Facades\Mail;

use Backpack\Feedback\app\Models\Feedback;

use App\Mail\Buy1ClickCreatedAdmin;
use App\Support\AdminNotificationResolver;

class FeedbackObserver
{
    /**
     * Handle the Feedback "created" event.
     *
     * @param  Backpack\Feedback\app\Models\Feedback  $feedback
     * @return void
     */
    public function created(Feedback $feedback)
    {
        $type = strtolower(trim((string) $feedback->type));

        if ($type !== '1_click_buy') {
            return;
        }

        $recipients = AdminNotificationResolver::resolve();

        if (empty($recipients)) {
            return;
        }

        Mail::to($recipients)->queue(new Buy1ClickCreatedAdmin($feedback));
    }

}
