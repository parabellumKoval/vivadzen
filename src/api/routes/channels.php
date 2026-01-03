<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('notifications.auth', function ($user) {
    return (bool) $user;
});

Broadcast::channel('notifications.user.{id}', function ($user, $id) {
    return (int) $user->getAuthIdentifier() === (int) $id;
});
