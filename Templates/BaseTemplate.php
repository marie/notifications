<?php

namespace NotificationSystem\Templates;

use NotificationSystem\Notification;
use NotificationSystem\NotificationSystemException;
use NotificationSystem\Template;
use NotificationSystem\Transport;

abstract class BaseTemplate implements Template
{
    /**
     * @var string
     */
    protected static $supportedNotification;

    /**
     * @var string[]
     */
    protected static $supportedTransports = [];

    /**
     * @var string[]
     */
    protected static $supportedLanguages = [];

    /**
     * Массив с заголовками сообщений на разных языках.
     * Например:
     *  [
     *      'ru' => 'Тема сообщения',
     *      'en' => 'Subject'
     *  ]
     * @var string[]
     */
    protected static $subject = [];

    public function getAllAvailableTransports()
    {
        return static::$supportedTransports;
    }

    /**
     * Можно ли этот шаблон отправить заданным транспортом?
     * @param Transport $transport
     * @return bool
     */
    public static function isAvailableForTransport(Transport $transport)
    {
        return in_array(get_class($transport), static::$supportedTransports);
    }

    /**
     * Можно ли этот шаблон отправить заданным транспортом?
     * @param string $transportName
     * @return bool
     */
    public static function isAvailableForTransportByClassName($transport)
    {
        return in_array($transport, static::$supportedTransports);
    }

    /**
     * Можно ли отправить сообщение с этим шаблоном с помощью данного транспорта?
     * @param Transport $transport
     * @throws NotificationSystemException
     */
    public function checkTransport(Transport $transport)
    {
        if (!static::isAvailableForTransport($transport)) {
            throw new NotificationSystemException('Шаблон (Template) и транспорт (Transport) несовместимы.');
        }
    }

    /**
     * Можно ли использовать этот шаблон для заданного уведомления?
     * @param Notification $notification
     * @return bool
     * @throws NotificationSystemException
     */
    public static function isAvailableForNotification(Notification $notification)
    {
        return self::isAvailableForNotificationByName(get_class($notification));
    }

    /**
     * Можно ли использовать этот шаблон для заданного уведомления?
     * @param string $notificationName
     * @return bool
     * @throws NotificationSystemException
     */
    public static function isAvailableForNotificationByName($notificationName)
    {
        return $notificationName == static::$supportedNotification;
    }

    /**
     * Проверяет задан ли язык уведомления и совместимость уведомления и шаблона
     * @param Notification $notification
     * @throws NotificationSystemException
     */
    public function checkNotification(Notification $notification)
    {
        if (!in_array($notification->language, static::$supportedLanguages)) {
            throw new NotificationSystemException(
                sprintf('Язык уведомления не задан или не поддерживается шаблоном [%s].', static::class)
            );
        }

        if (!static::isAvailableForNotification($notification)) {
            $templateName = get_class($this);
            $notificationName = get_class($notification);
            throw new NotificationSystemException("Шаблон [$templateName] и уведомление [$notificationName] несовместимы.");
        }
    }

    /**
     * $notification используется для подстановки переменных в файле шаблона
     * @param string $file
     * @param Notification $notification
     * @return string
     * @throws NotificationSystemException
     */
    protected function renderTemplateFile($file, Notification $notification)
    {
        if (!is_readable($file)) {
            throw new NotificationSystemException(sprintf('Файл [%s] не существует или не доступен для чтения.', $file));
        }

        ob_start();
        include $file;
        $content = ob_get_clean();

        return $content;
    }
}
