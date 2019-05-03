<?php

namespace NotificationSystem;

class Subscription
{
    /**
     * @var int
     */
    public $id;

    /**
     * id Profile'а, клиента или группы, являющейся владельцем подписки
     * @var int
     */
    public $ownerId;

    /**
     * id типа владельца (Profile, клиент, группа, базовый)
     * @var int
     * @see SubscriptionLevels
     */
    public $ownerType;

    /**
     * id уведомления
     * @var string
     * @see NotificationCodes
     */
    public $notificationCode;

    /**
     * id Транспорта
     * @var string
     * @see TransportCodes
     */
    public $transportCode;

    /**
     * id Шаблона
     * @var string
     * @see TemplateCodes
     */
    public $templateCode;

    /**
     * Дополнительный адрес для получения уведомления
     * Если он заполнен, то сообщение будет отправлено на него, а не на контакную информацию из Profile'а
     * @var string
     */
    public $address;

    /**
     * Свойство для "мягкого удаления"
     * @var int 1 or 0
     */
    public $del;
}
