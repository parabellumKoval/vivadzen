<?php

namespace App\Observers;

use Illuminate\Support\Facades\Mail;

use Backpack\Feedback\app\Models\Feedback;

use App\Mail\Buy1ClickCreatedAdmin;

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
      Mail::to(env('ADMIN_MAIL', 'info@kratomhelper.com'))->queue(new Buy1ClickCreatedAdmin($feedback));
    }

}
