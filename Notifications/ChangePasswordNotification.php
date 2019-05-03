<?php

namespace NotificationSystem\Notifications;

use NotificationSystem\Notification;

class ChangePasswordNotification extends Notification
{
    /**
     * @var string
     */
    public $managerName;

    /**
     * @var string
     */
    public $managerEmail;

    /**
     * @var string
     */
    public $profileLogin;

    /**
     * Название уведомления
     * @return string
     */
    public static function getNotificationTitle()
    {
        return 'Уведомление о смене пароля';
    }
}
