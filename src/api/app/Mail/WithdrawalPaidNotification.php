<?php

namespace App\Mail;

class WithdrawalPaidNotification extends WithdrawalNotification
{
    protected function subjectKey(): string
    {
        return 'mail.withdrawal.paid_subject';
    }

    protected function titleKey(): string
    {
        return 'mail.withdrawal.paid_title';
    }

    protected function viewName(): string
    {
        return 'mail.withdrawal_paid';
    }
}

