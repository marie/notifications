<?php

namespace NotificationSystem;

use Profile;

interface Transport
{
    /**
     * Метод выполняет все операции по отправке сообщения
     * @param Message $message
     * @return string
     */
    public function send(Message $message);

    /**
     * Проверяет корректность переданного адреса для конкретного транспорта
     * @param string $address
     * @return boolean
     */
    public function isValidAddress($address);

    /**
     * @param string $address
     * @return void
     */
    public function setAddress($address);

    /**
     * Записывает нужную контактную информацию из Profile
     * @param Profile $profile
     * @return void
     */
    public function setAddressFromProfile(Profile $profile);

    /**
     * @return mixed
     */
    public function getAddress();
}
