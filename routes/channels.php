<?php

use App\Enums\UserRole;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('role.tutor', fn ($user) => $user->role === UserRole::Tutor);

Broadcast::channel('role.guardian', fn ($user) => $user->role === UserRole::Guardian);
