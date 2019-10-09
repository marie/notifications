<?php

namespace NotificationSystem\Repositories\Mappers;

use db;
use NotificationSystem\SubscriptionCancel;

class SubscriptionCancelMapper extends BaseMapper
{
    /**
     * @var array
     * property => dbColumn
     */
    public static $fieldsMap = [
        'ownerId'        => 'ownerId',
        'ownerType'      => 'ownerType',
        'subscriptionId' => 'subscriptionId',
    ];

    /**
     * {@inheritDoc}
     */
    protected $tableName = '`NotificationSystem_SubscriptionCancel`';

    /**
     * Удаляет отмену подписки по связке полей subscriberId и subscriptionId
     * @param SubscriptionCancel $item
     */
    public function remove($item)
    {
        $sql = sprintf(
            "DELETE FROM %s WHERE `ownerId` = %d AND `subscriptionId` = %d AND `ownerType` = %d",
            $this->tableName,
            intval($item->ownerId),
            intval($item->subscriptionId),
            intval($item->ownerType)
        );

        db::query($sql);
    }

    /**
     * Создает объект SubscriptionCancel и заполняет его поля данными из БД
     * @param $array
     * @return SubscriptionCancel
     */
    public function makeEntity($array = [])
    {
        $subscriptionCancel = new SubscriptionCancel();

        fillObjectProperties($subscriptionCancel, $array, array_flip(static::$fieldsMap));

        return $subscriptionCancel;
    }
}
