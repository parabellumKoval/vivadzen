<?php

namespace App\Mail;

use App\Mail\Concerns\InteractsWithRegionalContext;
use App\Mail\Concerns\RoutesToEmailQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class VerifyEmailNotification extends Mailable
{
    use InteractsWithRegionalContext;
    use RoutesToEmailQueue;
    use Queueable, SerializesModels;

    /**
     * The user instance
     */
    public $user;

    /**
     * The verification URL
     */
    public $verificationUrl;

    /**
     * The regional context overrides
     */
    protected ?array $contextOverrides = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user = null, ?array $regionalContext = null)
    {
        $this->user = $user;
        $this->contextOverrides = $regionalContext;
        $this->routeToEmailQueue();
        $this->initializeRegionalContext($regionalContext);

        if (!$this->hasRegionalLocale()) {
            $this->setRegionalLocale(config('app.locale', 'uk'));
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(__('mail.verify_email.subject'))
            ->markdown('emails.verify-email', $this->buildViewData());
    }

    /**
     * Build view data for the email template
     */
    protected function buildViewData(): array
    {
        return $this->regionalViewData([
            'user' => $this->user,
            'verificationUrl' => $this->buildVerificationUrl(),
            'actionText' => __('mail.verify_email.button'),
            'greeting' => __('mail.verify_email.greeting'),
            'intro' => __('mail.verify_email.intro'),
            'outro' => __('mail.verify_email.outro'),
        ]);
    }

    /**
     * Build the verification URL
     */
    protected function buildVerificationUrl(): string
    {
        if ($this->user && method_exists($this->user, 'getEmailVerificationUrl')) {
            return $this->user->getEmailVerificationUrl();
        }

        // Generate the default verification URL
        $verificationUrl = \URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $this->user?->getKey(),
                'hash' => sha1($this->user?->getEmailForVerification()),
            ]
        );

        return $verificationUrl;
    }

    /**
     * Set the locale for the notification.
     *
     * @param string $locale
     * @return $this
     */
    public function locale($locale)
    {
        $this->setRegionalLocale($locale);
        return $this;
    }
}
