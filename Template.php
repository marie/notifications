<?php

namespace NotificationSystem;

interface Template
{
    /**
     * Содержание файла шаблона
     * @param Notification $notification
     * @return Message
     */
    public function render(Notification $notification);

    /**
     * Можно ли этот шаблон отправить заданным транспортом?
     * @param Transport $transport
     * @return bool
     */
    public static function isAvailableForTransport(Transport $transport);

    /**
     * Можно ли использовать этот шаблон для заданного уведомления?
     * @param Notification $notification
     * @return bool
     */
    public static function isAvailableForNotification(Notification $notification);

    /**
     * @param Notification $notification
     * @return void
     * @throws NotificationSystemException
     */
    public function checkNotification(Notification $notification);

    /**
     * @param Transport $transport
     * @return void
     * @throws NotificationSystemException
     */
    public function checkTransport(Transport $transport);
}
