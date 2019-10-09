<?php

namespace NotificationSystem;

use NotificationSystem\Repositories\SubscriptionCancelRepository;
use NotificationSystem\Repositories\SubscriptionRepository;
use Sentry;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class NotificationSystem
 * @package NotificationSystem
 */
class NotificationSystem
{
    /**
     * @var SubscriptionRepository
     */
    protected $subscriptionRepository;

    /**
     * @var TemplateRegistry
     */
    protected $templatesRegistry;

    /**
     * @var TransportRegistry
     */
    protected $transportRegistry;

    /**
     * @param SubscriptionRepository $subscriptionRepository
     * @param TemplateRegistry $templatesRegistry
     * @param TransportRegistry $transportRegistry
     * @param SubscriptionCancelRepository $subscriptionCancelRepository
     */
    public function __construct(
        SubscriptionRepository $subscriptionRepository,
        SubscriptionCancelRepository $subscriptionCancelRepository,
        TemplateRegistry $templatesRegistry,
        TransportRegistry $transportRegistry
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionCancelRepository = $subscriptionCancelRepository;
        $this->templatesRegistry = $templatesRegistry;
        $this->transportRegistry = $transportRegistry;
    }

    /**
     * Отправить сообщения по всем подпискам на уведомление
     *
     * @param Recipient $recipient
     * @param Notification $notification
     */
    public function sendNotification(Recipient $recipient, Notification $notification)
    {
        try {
            $this->sendNotificationOrFail($recipient, $notification);
        }
        catch (NotificationSystemException $e) {
            Sentry::captureException($e);
        }
    }

    /**
     * Отправить сообщения по всем подпискам на уведомление
     *
     * @param Recipient $recipient
     * @param Notification $notification
     * @throws NotificationSystemException
     */
    public function sendNotificationOrFail(Recipient $recipient, Notification $notification)
    {
        $this->debug(sprintf('[DEBUG] Запрошена отправка уведомления (уведомление - %s, профайл - %s)',
            get_class($notification),
            $recipient->getId()
        ));

        $activeSubscriptions = $this->getActiveSubscriptionsForRecipient($recipient, $notification);

        if (!$activeSubscriptions) {
            $this->debug(sprintf('[DEBUG] У профайла нет активных подписок (уведомление - %s, профайл - %s)',
                get_class($notification),
                $recipient->getId()
            ));
        }

        $fails = [];

        foreach ($activeSubscriptions as $subscription) {
            try {
                $this->sendNotificationForSubscription($recipient, $notification, $subscription);
            }
            catch (Exception $e) {
                $fails[] = $e->getMessage();
            }
        }

        if ($fails) {
            throw new NotificationSystemException(implode(PHP_EOL, $fails));
        }
    }

    /**
     * Метод отправляет сообщение по одной подписке
     * @param Recipient $recipient
     * @param Subscription $subscription
     * @param Notification $notification
     * @throws NotificationSystemException
     */
    public function sendNotificationForSubscription(
        Recipient $recipient,
        Notification $notification,
        Subscription $subscription
    ) {
        $this->debug(sprintf('[DEBUG] Обрабатывается подписка %s (уведомление - %s, профайл - %s)',
            get_class($subscription),
            get_class($notification),
            $recipient->getId()
        ));

        // 1. Получаем шаблон и транспорт для подписки
        $template = $this->templatesRegistry->getTemplateByCode($subscription->templateCode);

        $transport = $this->transportRegistry->getTransportByCode($subscription->transportCode);

        // 2. Проверяем совместимость транспорта, шаблона и уведомления
        if (!$template->isSupportedByNotification($notification)) {
            throw new NotificationSystemException('Шаблон (Template) и транспорт (Transport) несовместимы.');
        }

        if(!$template->isSupportedByTransport($transport)) {
            throw new NotificationSystemException('Шаблон (Template) и транспорт (Transport) несовместимы.');
        }

        // 3. Генерируем сообщение по шаблону
        $message = $template->render($notification);

        // 4. Устанавливаем адрес получателя
        if (!empty($subscription->address)) {
            $transport->setAddress($subscription->address);

            $this->debug(sprintf('[DEBUG] Адрес установлен из подписки "%s" (подписка - %s, уведомление - %s, профайл - %s, id подписки - %d)',
                $transport->getAddress(),
                get_class($subscription),
                get_class($notification),
                $recipient->getId(),
                $subscription->id
            ));
        } else {
            $transport->setAddressFromRecipient($recipient);

            $this->debug(sprintf('[DEBUG] Адрес установлен из профайла "%s" (подписка - %s, уведомление - %s, профайл - %s, id подписки - %d)',
                $transport->getAddress(),
                get_class($subscription),
                get_class($notification),
                $recipient->getId(),
                $subscription->id
            ));
        }

        try {
            $transport->send($message);
        } catch (NotificationSystemException $e) {
            \lc::log('notification_system', $e->getMessage());
        }

        $this->debug(sprintf('[DEBUG] Отправка сообщения (подписка - %s, уведомление - %s, профайл - %s)',
            $transport->getAddress(),
            get_class($subscription),
            get_class($notification),
            $recipient->getId()
        ));
    }

    private function debug($message)
    {
        // log
    }

    /**
     * Получить все подписки профиля, кроме отменённых
     * @param Recipient  $recipient
     * @param Notification $notification
     * @return array
     */
    public function getActiveSubscriptionsForRecipient($recipient, $notification)
    {
        $ownersList = [
            SubscriptionLevels::PROFILE => $recipient->getId(),
            SubscriptionLevels::GROUP   => $recipient->getGroupId(),
            SubscriptionLevels::CLIENT  => $recipient->getClientId(),
            SubscriptionLevels::BASE    => 0
        ];

        $subscriptions = $this->getAllSubscriptions($notification, $ownersList);

        $subscriptionsCancel = $this->getAllSubscriptionsCancel($subscriptions, $ownersList);

        return $this->filterOnlyActiveSubscriptions($subscriptions, $subscriptionsCancel);
    }

    /**
     * Возвращает все доступные для данного Receiver подписки
     * @param Notification $notification
     * @param array $ownersList
     * @return array
     */
    private function getAllSubscriptions($notification, $ownersList)
    {
        $subscriptions = $this->subscriptionRepository->findByNotificationAndOwnersList(
            $this->getCodeByNotification($notification),
            $ownersList
        );

        return $subscriptions;
    }

    /**
     * Возвращает все отмены подписок
     * @param array $subscriptions
     * @param array $ownersList
     * @return SubscriptionCancel[]
     */
    private function getAllSubscriptionsCancel($subscriptions, $ownersList)
    {
        $subscriptionsIdList = [];

        foreach ($subscriptions as $subscription) {
            $subscriptionsIdList[] = $subscription->id;
        }

        $subscriptionsCancel = $this->subscriptionCancelRepository->findBySubscriptionIdAndOwnersList(
            $subscriptionsIdList,
            $ownersList
        );

        return $subscriptionsCancel;
    }

    /**
     * Возвращает код уведомления по объекту класса
     *
     * @param $notification
     * @return int
     */
    protected function getCodeByNotification($notification)
    {
        return NotificationCodes::getCodeByNotification($notification);
    }

    /**
     * Returns filtered list of subscriptions from all levels
     * 
     * @param Subscription[] $subscriptions
     * @param SubscriptionCancel[] $subscriptionsCancel
     * @return Subscription[]
     */
    private function filterOnlyActiveSubscriptions($subscriptions, $subscriptionsCancel)
    {
        $cancelledSubscriptions = [];

        foreach ($subscriptionsCancel as $subscriptionCancel) {
            $cancelledSubscriptions[] = $subscriptionCancel->subscriptionId;
        }

        $result = [];

        foreach ($subscriptions as $subscription) {
            if (!in_array($subscription->id, $cancelledSubscriptions)) {
                $result[] = $subscription;
            }
        }

        return $result;
    }
}
