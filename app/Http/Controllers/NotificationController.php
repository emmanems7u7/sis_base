<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\NotificationInterface;

class NotificationController extends Controller
{
    protected $NotificationRepository;
    public function __construct(NotificationInterface $NotificationRepository)
    {
        $this->NotificationRepository = $NotificationRepository;
    }
    public function markAsRead($notificationId)
    {

        $this->NotificationRepository->markAsRead($notificationId);


    }
}
