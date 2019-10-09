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
     * Записывает нужную контактную информацию из Recipient
     * @param Recipient $recipient
     * @return void
     */
    public function setAddressFromRecipient(Recipient $recipient);

    /**
     * @return mixed
     */
    public function getAddress();
}
