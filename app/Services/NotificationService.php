<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public static function notify(
        User|int $user,
        string $type,
        string $title,
        string $body,
        ?string $actionUrl = null
    ): UserNotification {
        $userId = $user instanceof User ? $user->id : $user;

        return UserNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'action_url' => $actionUrl,
        ]);
    }

    public static function notifyAdmins(string $type, string $title, string $body, ?string $actionUrl = null): void
    {
        User::where('role', User::ROLE_ADMIN)
            ->where('is_active', true)
            ->pluck('id')
            ->each(fn (int $adminId) => self::notify($adminId, $type, $title, $body, $actionUrl));
    }
}
