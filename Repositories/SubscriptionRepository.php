<?php

namespace NotificationSystem\Repositories;

use db;
use NotificationSystem\Subscription;
use NotificationSystem\SubscriptionLevels;

class SubscriptionRepository extends BaseRepository
{
    /**
     * "Мягкое удаление", ставит только пометку del в базе данных
     * @param Subscription $item
     */
    public function softRemove($item)
    {
        $item->del = 1;
        $this->dataMapper->update($item);
    }

    /**
     * Ищет подписки, созданные только на уровне Profile
     * @param int $profileId
     * @return array of Subscription
     */
    public function findProfileLevelSubscriptions($profileId)
    {
        return $this->findByOwnerIdAndOwnerType($profileId, SubscriptionLevels::PROFILE);
    }

    /**
     * Ищет подписки, созданные только на уровне Group
     * @param int $groupId
     * @return array of Subscription
     */
    public function findGroupLevelSubscriptions($groupId)
    {
        return $this->findByOwnerIdAndOwnerType($groupId, SubscriptionLevels::GROUP);
    }

    /**
     * Ищет подписки, созданные только на уровне Client
     * @param int $clientId
     * @return array of Subscription
     */
    public function findClientLevelSubscriptions($clientId)
    {
        return $this->findByOwnerIdAndOwnerType($clientId, SubscriptionLevels::CLIENT);
    }

    /**
     * Ишет подписки, созданные на базовом уровне
     * @return array of Subscription
     */
    public function findBaseLevelSubscriptions()
    {
        return $this->findByOwnerIdAndOwnerType(0, SubscriptionLevels::BASE);
    }

    /**
     * Возвращает все подписки, доступные для Profile (даже подписки, созданные и доступные для этого Profile на других уровнях
     * @param int $notificationCode
     * @param array $ownersList [ownerType => ownerId]
     * @return array of Subscription
     */
    public function findByNotificationAndOwnersList($notificationCode, $ownersList)
    {
        $list = [];

        if (!($notificationCode && $ownersList)) {
            return [];
        }

        foreach ($ownersList as $ownerType => $ownerId) {
            $list[] = sprintf("(`ownerType` = '%d' AND `ownerId` = '%d')", $ownerType, $ownerId);
        }

        $list = implode(' OR ', $list);

        $sql = sprintf(
            "SELECT * FROM %s WHERE `notificationCode` = '%s' AND (%s) AND `del` = 0",
            $this->tableName,
            db::escape($notificationCode),
            $list
        );

        $result = db::query($sql);

        $subscriptions = [];

        while ($row = db::fetch_assoc($result)) {
            $subscriptions[$row['id']] = $this->dataMapper->makeEntity($row);
        }

        return $subscriptions;
    }

    /**
     * Возвращает подписки для одного владельца на одном уровне
     * @param int $ownerId
     * @param int $ownerType
     * @return array
     */
    private function findByOwnerIdAndOwnerType($ownerId, $ownerType)
    {
        $ownerId = intval($ownerId);

        $ownerType = db::escape($ownerType);

        $sql = sprintf(
            "SELECT * FROM %s WHERE `ownerId` = %d AND `ownerType` = %s AND `del` = 0",
            $this->tableName,
            $ownerId,
            $ownerType
        );

        $result = db::query($sql);

        $subscriptions = [];

        while ($row = db::fetch_assoc($result)) {
            $subscriptions[$row['id']] = $this->dataMapper->makeEntity($row);
        }

        return $subscriptions;
    }
}
