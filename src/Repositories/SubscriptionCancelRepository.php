<?php

namespace NotificationSystem\Repositories;

use db;
use NotificationSystem\Repositories\Mappers\SubscriptionCancelMapper;
use NotificationSystem\SubscriptionCancel;

class SubscriptionCancelRepository extends BaseRepository
{
    /**
     * @var SubscriptionCancelMapper
     */
    protected $dataMapper;

    /**
     * Находит все отмены подписок для владельца
     *
     * @param int $ownerId
     * @param int $ownerType
     * @return array of SubscriptionCancel
     */
    public function findByOwner($ownerId, $ownerType)
    {
        $sql = sprintf(
            "SELECT * FROM %s WHERE `ownerId` = %d AND `ownerType` = %d",
            $this->tableName,
            intval($ownerId),
            intval($ownerType)
        );

        $result = db::query($sql);

        $subscriptionCancel = [];

        while ($row = db::fetch_assoc($result)) {
            $subscriptionCancel[] = $this->dataMapper->makeEntity($row);
        }

        return $subscriptionCancel;
    }

    /**
     * Находит отмену для конкретного владельца и конкретной подписки
     *
     * @param int $subscriptionId
     * @param int $ownerId
     * @param $ownerType
     * @return SubscriptionCancel
     */
    public function findSubscriptionForOwner($subscriptionId, $ownerId, $ownerType)
    {
        $sql = sprintf(
            "SELECT * FROM %s WHERE `subscriptionId` = %d AND `ownerId` = %d AND `ownerType` = %d",
            $this->tableName,
            intval($subscriptionId),
            intval($ownerId),
            intval($ownerType)
        );

        $result = db::query($sql);

        if ($row = db::fetch_assoc($result)) {
            return $this->dataMapper->makeEntity($row);
        }
    }

    /**
     * Ищет во всех уровнях, есть ли отмена для подписки у данного владельца
     * @param int[] $subscriptionsIdList
     * @param int[] $ownersList
     * @return SubscriptionCancel[]
     */
    public function findBySubscriptionIdAndOwnersList($subscriptionsIdList, $ownersList)
    {
        $ownerTypes = implode(', ',  array_keys($ownersList));

        $ownerIds = array_filter($ownersList, function ($id) {
            return !is_null($id);
        });

        $ownerIds = implode(', ',  $ownerIds);

        $subscriptionsIdList = implode(', ', $subscriptionsIdList);

        if (!($subscriptionsIdList && $ownerTypes && $ownerIds)) {
            return [];
        }

        $sql = sprintf(
            "SELECT * FROM %s WHERE `subscriptionId` IN (%s) AND `ownerType` IN (%s) AND `ownerId` IN (%s)",
            $this->tableName,
            $subscriptionsIdList,
            $ownerTypes,
            $ownerIds
        );

        $result = db::query($sql);

        $subscriptionCancel = [];

        while ($row = db::fetch_assoc($result)) {
            $subscriptionCancel[] = $this->dataMapper->makeEntity($row);
        }

        return $subscriptionCancel;
    }
}
