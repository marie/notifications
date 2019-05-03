<?php

namespace NotificationSystem;

class SubscriptionCancel
{
    /**
     * id владельца (Profile'а, группы или клиента), который отменил подписку
     * @var int
     */
    public $ownerId;

    /**
     * id типа владельца (Profile, клиент, группа)
     * @var int
     * @see SubscriptionLevels
     */
    public $ownerType;

    /**
     * @var int
     */
    public $subscriptionId;
}
