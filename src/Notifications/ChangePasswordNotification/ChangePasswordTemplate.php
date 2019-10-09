<?php

namespace NotificationSystem\Notifications\ChangePasswordNotification;

use NotificationSystem\Message;
use NotificationSystem\Notification;
use NotificationSystem\Notifications\ChangePasswordNotification;
use NotificationSystem\NotificationSystemException;
use NotificationSystem\Templates\BaseTemplate;
use NotificationSystem\Transports\EmailTransport;

class ChangePasswordTemplate extends BaseTemplate
{
    /**
     * @var string
     */
    protected static $supportedNotification = ChangePasswordNotification::class;

    /**
     * @var string[]
     */
    protected static $supportedTransports = [
        EmailTransport::class
    ];

    /**
     * @var string[]
     */
    protected static $supportedLanguages = [
        'ru',
        'en',
    ];

    /**
     * Массив с заголовками сообщений на разных языках.
     * Например:
     *  [
     *      'ru' => 'Тема сообщения',
     *      'en' => 'Subject'
     *  ]
     * @var array
     */
    protected static $subject = [
        'ru' => 'Изменение пароля',
        'en' => 'Password restore',
    ];

    /**
     * @param Notification $notification
     * @return string содержание файла шаблона
     * @throws NotificationSystemException
     */
    public function render(Notification $notification)
    {
        $file = __DIR__ . '/ChangePassword_' . $notification->language . '.php';

        $body = $this->renderTemplateFile($file, $notification);

        return new Message(self::$subject[$notification->language], $body);
    }
}
