<?php

namespace NotificationSystem;

/**
 * Класс содержит список всех доступных уведомлений
 * @package NotificationSystem\Notification
 */
abstract class NotificationCodes
{
    /**
     * [code => NotificationClass]
     * @var array
     */
    private static $codesMap = [
        10 => RestorePasswordNotification::class,
        20 => ChangePasswordNotification::class,
        30 => OrderCreatedNotification::class,
        40 => TravellerChoseApproverNotification::class,
        50 => ApproverConfirmedOrderNotification::class,
        60 => ApproverRejectedOrderNotification::class,
    ];

    /**
     * По идентификатору возвращает полное имя класса
     * @param int $code
     * @return string
     * @throws NotificationSystemException
     */
    public static function getNotificationClassByCode($code)
    {
        if (!isset(self::$codesMap[$code])) {
            throw new NotificationSystemException(sprintf('Уведомление с кодом [%d] не зарегистрировано.', $code));
        }

        return self::$codesMap[$code];
    }

    /**
     * Возвращает код уведомления по имени класса
     * @param string $notificationClass
     * @return int
     * @throws NotificationSystemException
     */
    public static function getCodeByNotificationClass($notificationClass)
    {
        $code = array_search($notificationClass, self::$codesMap);

        if ($code === false) {
            throw new NotificationSystemException(sprintf(
                'Код для уведомления [%s] не зарегистрирован.',
                $notificationClass
            ));
        }

        return $code;
    }

    /**
     * Возвращает код уведомления по объекту класса
     * @param Notification $notification
     * @return int
     */
    public static function getCodeByNotification($notification)
    {
        $notificationClass = get_class($notification);

        return self::getCodeByNotificationClass($notificationClass);
    }

    /**
     * @return array
     */
    public static function getNotificationList()
    {
        return self::$codesMap;
    }
}
