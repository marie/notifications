<?php

namespace NotificationSystem;

use NotificationSystem\Templates\BaseTemplate;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use NotificationSystem\Notifications\ChangePasswordNotification;
use NotificationSystem\Repositories\SubscriptionCancelRepository;
use NotificationSystem\Repositories\SubscriptionRepository;

/**
 * @group unit
 */
class NotificationSystemTest extends TestCase
{
    protected function makeNotificationSystem($transport, $subscriptions = [], $subscriptionCancels = [])
    {
        // 1. Subscription's repository stub
        $subscriptionRepository = $this->prophesize(SubscriptionRepository::class);
        $subscriptionRepository->findByNotificationAndOwnersList(
            Argument::type('integer'),
            Argument::type('array')
        )->willReturn($subscriptions);

        // 2. Cancelled subscription's repository stub
        $subscriptionCancelRepository = $this->prophesize(SubscriptionCancelRepository::class);
        $subscriptionCancelRepository->findBySubscriptionIdAndOwnersList(
            Argument::type('array'),
            Argument::type('array')
        )->willReturn($subscriptionCancels);

        // 3. Template registry's stub
        $template = $this->prophesize(BaseTemplate::class);
        $template->isSupportedByNotification(Argument::type(Notification::class))->willReturn(true);
        $template->isSupportedByTransport(Argument::type(Transport::class))->willReturn(true);
        $template->render(Argument::type(Notification::class))->willReturn(new Message(null, null));

        $templateRegistry = $this->prophesize(TemplateRegistry::class);
        $templateRegistry->getTemplateByCode(Argument::type('integer'))->willReturn($template);

        // 4. Transport registry's stub
        $transportRegistry = $this->prophesize(TransportRegistry::class);
        $transportRegistry->makeTransportByCode(Argument::type('integer'))->willReturn($transport);

        $notificationSystem = new FakeNotificationSystem(
            $subscriptionRepository->reveal(),
            $subscriptionCancelRepository->reveal(),
            $templateRegistry->reveal(),
            $transportRegistry->reveal()
        );

        return $notificationSystem;
    }

    protected function makeRecipientWithEmail($email)
    {
        $recipient = $this->prophesize(Recipient::class);
        $recipient->getId()->willReturn(1);
        $recipient->getGroupId()->willReturn(2);
        $recipient->getClientId()->willReturn(3);
        $recipient->getEmail()->willReturn($email);

        return $recipient->reveal();
    }

    public function test_sendNotification_methodSendNotificationForSubscriptionCalled()
    {
        // Arrange
        $profile = $this->makeRecipientWithEmail('test@test');
        $notification = $this->prophesize(Notification::class)->reveal();

        $transport = $this->prophesize(Transport::class);

        $subscription = new Subscription();
        $subscription->templateCode = 10;
        $subscription->transportCode = 100;
        $subscription->notificationCode = 10;

        $subscription2 = new Subscription();
        $subscription2->templateCode = 10;
        $subscription2->transportCode = 100;
        $subscription2->notificationCode = 10;

        $notificationSystem = $this->makeNotificationSystem($transport, [$subscription, $subscription2]);

        // Act
        $notificationSystem->sendNotification($profile, $notification);

        // Assert
        $transport->send(new Message(null, null))->shouldBeCalledTimes(2);
    }

    public function test_sendNotificationForSubscription_subscriptionWithAddress_subscriptionsAddressUsed()
    {
        // Arrange
        $profile = $this->makeRecipientWithEmail('profile@email.com');
        $notification = $this->prophesize(Notification::class)->reveal();

        $subscription = new Subscription();
        $subscription->address = 'testEmail@mail.com'; // a subscription with predefined address
        $subscription->templateCode = 10;
        $subscription->transportCode = 100;

        $transport = $this->prophesize(Transport::class);

        $notificationSystem = $this->makeNotificationSystem($transport->reveal());

        // Act
        $notificationSystem->sendNotificationForSubscription($profile, $notification, $subscription);

        // Assert
        $transport->setAddress('testEmail@mail.com')->shouldBeCalled();
    }

    public function test_sendNotificationForSubscription_subscriptionWithoutAddress_AddressFromRecipientUsed()
    {
        // Arrange
        $recipient = $this->makeRecipientWithEmail('profile@email.com');
        $notification = $this->prophesize(Notification::class)->reveal();

        $subscription = new Subscription();
        $subscription->ownerType = 4;
        $subscription->ownerId = 11;
        $subscription->templateCode = 10;
        $subscription->transportCode = 100;

        $transport = $this->prophesize(Transport::class);

        $notificationSystem = $this->makeNotificationSystem($transport->reveal(), [$subscription]);

        // Act
        $notificationSystem->sendNotificationForSubscription($recipient, $notification, $subscription);

        // Assert
        $transport->setAddressFromRecipient($recipient)->shouldHaveBeenCalled();
    }

    public function test_sendNotificationForSubscription_normally_transportSendCalled()
    {
        // Arrange
        $profile = $this->makeRecipientWithEmail('profile@email.com');
        $notification = $this->prophesize(Notification::class)->reveal();

        $subscription = $this->prophesize(Subscription::class);
        $subscription->templateCode = 10;
        $subscription->transportCode = 100;

        $transport = $this->prophesize(Transport::class);

        $notificationSystem = $this->makeNotificationSystem($transport->reveal());

        // Act
        $notificationSystem->sendNotificationForSubscription($profile, $notification, $subscription->reveal());

        // Assert
        $transport->send(new Message(null, null))->shouldBeCalled();
    }

    public function test_getActiveSubscriptionsForProfile_twoSubscriptionsAndOneSubscriptionCancel_returnsOneSubscription()
    {
        // Arrange
        $recipient = $this->makeRecipientWithEmail('meow@email.ru');

        $notification = $this->prophesize(ChangePasswordNotification::class)->reveal();

        $subscription = new Subscription();
        $subscription->id = 123;
        $subscriptions[] = $subscription;

        $subscription2 = new Subscription();
        $subscription2->id = 456;
        $subscriptions[] = $subscription2;

        $subscriptionCancel = new SubscriptionCancel();
        $subscriptionCancel->subscriptionId = 123;

        $transport = $this->prophesize(Transport::class);

        $notificationSystem = $this->makeNotificationSystem($transport, $subscriptions, [$subscriptionCancel]);

        // Act
        $result = $notificationSystem->getActiveSubscriptionsForRecipient($recipient, $notification);

        // Assert
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Subscription::class, $result[0]);
        $this->assertEquals(456, $result[0]->id);
    }
}

class FakeNotificationSystem extends NotificationSystem
{
    protected function getCodeByNotification($notification)
    {
        return 100;
    }
}