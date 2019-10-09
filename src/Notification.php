<?php

namespace NotificationSystem;

abstract class Notification
{
    /**
     * @var string
     */
    public $language;

    /**
     * Заголовок уведомления для пользователей
     * Переопределить в дочерних классах на понятное для пользователей название
     * @return string
     */
    public static function getNotificationTitle()
    {
        return static::class;
    }

    /**
     * Код уведомления
     * @return string
     */
    public function getCode()
    {
        return NotificationCodes::getCodeByNotification($this);
    }
}
