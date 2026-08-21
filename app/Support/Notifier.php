<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\User;

class Notifier
{
    public static function notify(User $user, string $type, string $message, ?int $workOrderId = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'message' => $message,
            'related_work_order_id' => $workOrderId,
        ]);
    }

    public static function notifyMany(iterable $users, string $type, string $message, ?int $workOrderId = null): void
    {
        foreach ($users as $user) {
            self::notify($user, $type, $message, $workOrderId);
        }
    }
}
