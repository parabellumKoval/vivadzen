<?php

namespace App\Support;

class MailRecipientResolver
{
    public static function email($recipient): ?string
    {
        if (! $recipient) {
            return null;
        }

        $email = static::valueAsString(data_get($recipient, 'email'));

        if ($email === '' && method_exists($recipient, 'profile')) {
            $email = static::valueAsString(data_get($recipient->profile, 'email'));
        }

        if ($email === '') {
            return null;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) ?: null;
    }

    protected static function valueAsString(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return trim($value);
    }
}

