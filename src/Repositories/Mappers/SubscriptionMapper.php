<?php

namespace NotificationSystem\Repositories\Mappers;

use NotificationSystem\Subscription;
use NotificationSystem\SubscriptionLevels;
use db;

class SubscriptionMapper extends BaseMapper
{
    /**
     * @var array
     * property => dbColumn
     */
    public static $fieldsMap = [
        'id'               => 'id',
        'address'          => 'address',
        'ownerId'          => 'ownerId',
        'ownerType'        => 'ownerType',
        'notificationCode' => 'notificationCode',
        'templateCode'     => 'templateCode',
        'transportCode'    => 'transportCode',
        'del'              => 'del',
    ];

    /**
     * {@inheritDoc}
     */
    protected $tableName = '`NotificationSystem_Subscription`';

    /**
     * Создает объект Subscription и заполняет его поля данными из БД
     * @param $array
     * @return Subscription
     */
    public function makeEntity($array = [])
    {
        $subscription = new Subscription();

        fillObjectProperties($subscription, $array, static::$fieldsMap);

        return $subscription;
    }
}
