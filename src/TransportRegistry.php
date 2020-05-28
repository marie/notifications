<?php

namespace NotificationSystem;

use NotificationSystem\Transports\EmailTransport;

/**
 * Класс содержит список доступных транспортов.
 * @package NotificationSystem
 */
class TransportRegistry
{
    /**
     * @var array
     */
    protected $registry = [];

    /**
     * @var array
     */
    protected $codesMap = [];

    /**
     * Возвращает транспорт из кэша по числовому идентификатору (если его там нет, то создает, добавляет в кэш и возвращает)
     * @param string $transportCode
     * @return Transport
     * @throws NotificationSystemException
     */
    public function makeTransportByCode($transportCode)
    {
        $transportClass = $this->getTransportClassByCode($transportCode);

        switch ($transportClass) {
            case EmailTransport::class: return new EmailTransport(new MailSender());
            default: throw new NotificationSystemException('Thansport is not supported.');
        }
    }

    /**
     * Возвращает полное имя класса Транспорта по его числовому идентификатору
     * @param int $code
     * @return Transport
     * @throws NotificationSystemException
     */
    public function getTransportClassByCode($code)
    {
        if (!isset($this->codesMap[$code])) {
            throw new NotificationSystemException(sprintf('Transport with code [%d] is not registered.', $code));
        }

        return $this->codesMap[$code];
    }

    /**
     * Список Транспортов в виде [code => "Описание Транспорта"]
     * @return array
     */
    public function getTransportList()
    {
        $result = [];

        foreach ($this->codesMap as $code => $className) {
            $result[$code] = $this->registry[$className];
        }

        return $result;
    }

    /**
     * Устанавливает начальные данные (доступные для использования транспорты)
     */
    public function setupDefaultTransports()
    {
        $this->registerTransport(1, EmailTransport::class, 'Email');
    }

    /**
     * В качестве параметра принимает имя класса ClassName::class
     * @param $code
     * @param string $transport
     * @param $description
     * @throws NotificationSystemException
     */
    public function registerTransport($code, $transport, $description)
    {
        if (!class_exists($transport, true) || !is_subclass_of($transport, Transport::class)) {
            throw new NotificationSystemException('Class is not a transport or does not exist');
        }

        $this->codesMap[$code] = $transport;
        $this->registry[$transport] = $description;
    }
}
