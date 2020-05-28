<?php

namespace NotificationSystem;

use NotificationSystem\Notification;
use NotificationSystem\Templates\BaseTemplate;
use NotificationSystem\Transports\EmailTransport;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class TemplateTest extends TestCase
{
    public function testIsAvailableForTransport_transportExists_returnsTrue()
    {
        $template = new FakeTemplate();

        // Assert
        $this->assertTrue($template::isSupportedByTransport(new FakeTransport(new MailSender())));
    }

    public function testIsAvailableForTransport_transportNotExists_returnsFalse()
    {
        $template = new FakeTemplate();

        // Assert
        $this->assertFalse($template->isSupportedByTransport(new FakeAnotherTransport(new MailSender())));
    }

    public function test_sendNotificationForSubscription_templateIsIncompatible_throwsException()
    {
        // Assert
        $this->expectExceptionMessageMatches(
            '/^The Template and The Transport can not be used together.$/'
        );

        $template = new FakeTemplate();

        $template->checkTransport(new FakeAnotherTransport(new MailSender()));
    }

    public function test_isAvailableForNotification_notificationIsCorrect_returnsTrue()
    {
        // Arrange
        $template = new FakeTemplate();

        $notification = new FakeNotification();
        $notification->language = 'ru';

        // Assert
        $this->assertTrue($template->isAvailableForNotification($notification));
    }

    public function test_isAvailableForNotification_languageIsNotSupported_returnsFalse()
    {
        // Arrange
        $template = new FakeTemplate();

        $notification = new FakeNotification();
        $notification->language = 'jp';

        // Act
        $this->assertFalse($template->isSupportedByNotification($notification));
    }
}

class FakeTemplate extends BaseTemplate
{
    /**
     * @var string
     */
    protected static $supportedNotification = FakeNotification::class;

    /**
     * @var string[]
     */
    protected static $supportedTransports = [
        FakeTransport::class
    ];

    /**
     * @var string[]
     */
    protected static $supportedLanguages = [
        'ru',
        'en'
    ];

    /**
     * @param Notification $notification
     * @return string содержание файла шаблона
     */
    public function render(Notification $notification)
    {
        $this->renderTemplateFile($this->file, $notification);
    }
}

class FakeNotification extends Notification
{
}

class FakeTransport extends EmailTransport
{
}

class FakeAnotherTransport extends EmailTransport
{
}