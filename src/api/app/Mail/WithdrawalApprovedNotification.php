<?php

namespace App\Mail;

class WithdrawalApprovedNotification extends WithdrawalNotification
{
    protected function subjectKey(): string
    {
        return 'mail.withdrawal.approved_subject';
    }

    protected function titleKey(): string
    {
        return 'mail.withdrawal.approved_title';
    }

    protected function viewName(): string
    {
        return 'mail.withdrawal_approved';
    }
}

