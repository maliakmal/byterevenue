<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('public-event-channel', function () {
    return true;
});

Broadcast::channel('admin-event-channel', function () {
    return auth()->check() && auth()->user()->isAdmin();
});
