<?php

namespace App\Repositories;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\NotificationInterface;

class NotificationRepository implements NotificationInterface
{
    public function markAsRead ($notificationId)
    {
        $user = Auth::user();

        // Marcar la notificación como leída
        $notification = $user->notifications()->where('id', $notificationId)->first();
        if ($notification) {
            $notification->markAsRead();
        }

    }
    
   
}