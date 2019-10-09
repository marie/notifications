<?php

namespace NotificationSystem\Repositories;

use NotificationSystem\Repositories\Mappers\BaseMapper;
use NotificationSystem\Subscription;
use NotificationSystem\SubscriptionCancel;

abstract class BaseRepository
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var BaseMapper
     */
    protected $dataMapper;

    /**
     * @param BaseMapper $dataMapper
     */
    public function __construct(BaseMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
        $this->tableName = $this->dataMapper->getTableName();
    }

    /**
     * Передает объект в DataMapper для добавления
     * @param $item
     * @return void
     */
    public function add($item)
    {
        $this->dataMapper->insert($item);
    }

    /**
     * Передает объект в DataMapper для обновления
     * @param $item
     * @return void
     */
    public function update($item)
    {
        $this->dataMapper->update($item);
    }

    /**
     * Если объект уже находится в БД и имеет id, то он обновляется,
     * если объект без id, то он добавляется
     * @param Subscription|SubscriptionCancel $item
     * @return void
     */
    public function save($item)
    {
        if ($item->id) {
            $this->dataMapper->update($item);
        } else {
            $this->dataMapper->insert($item);
        }
    }

    /**
     * Удаляет объект по id, если id есть, а если нет, то удаляет запись по совпадающим полям
     * @param Subscription|SubscriptionCancel $item
     * @return void
     */
    public function remove($item)
    {
        if (isset($item->id)) {
            $this->dataMapper->removeById($item->id);
        } else {
            $this->dataMapper->remove($item);
        }
    }

    /**
     * Ищет запись по id
     * @param int $id
     * @return Subscription|SubscriptionCancel entity
     */
    public function findById($id)
    {
        return $this->dataMapper->find($id);
    }
}
