<?php

namespace NotificationSystem\Repositories\Mappers;

use db;
use NotificationSystem\NotificationSystemException;

abstract class BaseMapper
{
    /**
     * @var array
     * property => dbColumn
     */
    public static $fieldsMap = [];

    /**
     * @var string Имя таблицы в базе данных
     */
    protected $tableName;

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Ищет в базе данных по полю id
     * @param int $id
     * @return mixed Объект по id в БД, тип объекта в зависимости от DataMapper'а
     */
    public function find($id)
    {
        $sql = sprintf(
            "SELECT * FROM %s WHERE `id` = %d",
            $this->tableName,
            intval($id)
        );

        $result = db::query($sql);
        $row = db::fetch_assoc($result);

        if (!$row) {
            return;
        }

        return $this->makeEntity($row);
    }

    /**
     * Вставляет объект
     * @param $item
     * @return void
     */
    public function insert($item)
    {
        $sqlData = $this->getSqlDataRow($item);

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->tableName,
            join(', ', array_keys($sqlData)),
            join(', ', $sqlData)
        );

        db::query($sql);

        if (db::affected()) {
            $item->id = db::insert_id();
        }
    }

    /**
     * Обновляет запись
     * @param $item
     */
    public function update($item)
    {
        $sqlData = $this->getSqlDataRow($item);
        $sqlParamSet = [];

        foreach ($sqlData as $column => $value) {
            $sqlParamSet[] = sprintf('%s = %s', $column, $value);
        }

        $data = join(", ", $sqlParamSet);

        $sql = sprintf(
            "UPDATE %s SET %s WHERE id = %d",
            $this->tableName,
            $data,
            $item->id
        );

        db::query($sql);
    }

    /**
     * Удаляет запись
     * @param $item
     * @return void
     */
    public function remove($item)
    {
        $sqlData = $this->getSqlDataRow($item);
        $sqlParamSet = [];

        foreach ($sqlData as $column => $value) {
            $sqlParamSet[] = sprintf('%s = %s', $column, $value);
        }

        $data = join(", ", $sqlParamSet);

        $sql = sprintf(
            "DELETE FROM %s WHERE %s",
            $this->tableName,
            $data
        );

        db::query($sql);
    }

    /**
     * Удаляет запись по id
     * @param $id
     * @return void
     */
    public function removeById($id)
    {
        $sql = sprintf(
            "DELETE FROM %s WHERE `id` = %d",
            $this->tableName,
            intval($id)
        );

        db::query($sql);
    }

    /**
     * Преобразует объект в массив полей для базы данных
     * @param $item
     * @return array
     * @throws NotificationSystemException
     */
    protected function getSqlDataRow($item)
    {
        $sqlData = [];
        foreach (static::$fieldsMap as $property => $field) {
            if (!property_exists($item, $property)) {
                throw new NotificationSystemException("Объект не содержит свойство {$property}, поэтому не может быть сохранён.");
            }

            $column = sprintf('`%s`', $field);
            $value = $item->{$property};

            if (is_null($value)) {
                $sqlData[$column] = 'NULL';
            } else {
                $sqlData[$column] = sprintf("'%s'", db::escape($value));
            }
        }

        return $sqlData;
    }

    /**
     * @param $array
     * @return Object Объект того типа, который должен возвращать DataMapper
     */
    abstract public function makeEntity($array = []);
}
