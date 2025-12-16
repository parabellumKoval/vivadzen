<?php

namespace App\Notifications;

use App\Mail\Concerns\InteractsWithRegionalContext;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use InteractsWithRegionalContext;
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(?array $regionalContext = null)
    {
        $this->initializeRegionalContext($regionalContext);

        if (!$this->hasRegionalLocale()) {
            $this->setRegionalLocale(config('app.locale', 'uk'));
        }
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Ensure the regional context locale is applied before generating mail content
        // This is critical for queue processing where app locale might be different
        if ($this->regionalLocale()) {
            app()->setLocale($this->regionalLocale());
        }
        
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('mail.verify_email.subject'))
            ->markdown('emails.verify-email', [
                'greeting' => __('mail.verify_email.greeting'),
                'intro' => __('mail.verify_email.intro'),
                'actionText' => __('mail.verify_email.button'),
                'actionUrl' => $verificationUrl,
                'outro' => __('mail.verify_email.outro'),
                'user' => $notifiable,
            ]);
    }

    /**
     * Get the verification URL.
     */
    protected function verificationUrl($notifiable): string
    {
        return \URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
