<?php

namespace NotificationSystem\Transports;

use NotificationSystem\MailSender;
use NotificationSystem\NotificationSystemException;
use NotificationSystem\Message;
use NotificationSystem\Recipient;
use NotificationSystem\Transport;

class EmailTransport implements Transport
{
    /**
     * Адрес, куда транспорт будет отправлять сообщение
     * @var string
     */
    protected $address;

    /**
     * @var MailSender
     */
    protected $mailSender;

    /**
     * @var string
     */
    private $nameFrom = 'site';

    /**
     * @var string
     */
    private $emailFrom = 'no-reply@site';

    public function __construct(MailSender $mailSender)
    {
        $this->mailSender = $mailSender;
    }

    /**
     * Подготовалиывает сообщение для отправки и отправляет его
     * @param Message $message
     * @return void
     * @throws NotificationSystemException when address isn't correct or can't be used by this type of transport
     */
    public function send(Message $message)
    {
        if (!$this->isValidAddress($this->address)) {
            throw new NotificationSystemException('Неправильный адрес: ' . $this->address);
        }

        // получаем тело сообщения
        $body = $message->getBody();

        $this->mailSender->setFrom($this->nameFrom, $this->emailFrom);
        $this->mailSender->setTo($this->address);

        $this->mailSender->setContentType('html');
        $this->mailSender->setSubject($message->getTitle());

        // находим все картинки внутри сообщения <img src="">
        $relatedFiles = $this->findRelatedImages($body);

        // и заменяем их на cid:imageIdentificator для того чтобы потом они корректно отображались в письме
        $body = $this->prepareRelatedImages($body, $relatedFiles);

        // проходим циклом по всем прикрепляемым файлам, и если файлы существуют, то добавляем их к сообщению
        $attachedFiles = $message->getAttachments();
        $this->addAttachments($attachedFiles);

        // тело сообщения добавляем к отправляемому сообщению
        $this->mailSender->setContent($body);

        $this->mailSender->send();
    }

    /**
     * Корректный ли email был передан?
     * @param string $address
     * @return boolean
     */
    public function isValidAddress($address)
    {
        return filter_var($address, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param string $address
     * @return void
     */
    public function setAddress($address)
    {
        $this->address = trim($address);
    }

    /**
     * @param Recipient $recipient
     * @return void
     * @throws NotificationSystemException
     */
    public function setAddressFromRecipient(Recipient $recipient)
    {
        $email = $recipient->getEmail();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new NotificationSystemException('Email is not valid');
        }

        $this->address = $email;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Находит все картинки в сообщении и возвращает ссылки на них
     * @param string $body
     * @return array
     */
    private function findRelatedImages($body)
    {
        preg_match_all("/<img src=[\"|']([\w\d\/\.\-,?!]*)[\"|'].*>/", $body, $matches);

        return isset($matches[1]) ? $matches[1] : [];
    }

    /**
     * Заменяет ссылки на картинки на cid:imageIdentificator для того чтобы потом они корректно отображались в письме
     * @param string $body
     * @param array of paths $relatedFiles
     * @return string
     */
    private function prepareRelatedImages($body, $relatedFiles)
    {
        array_walk($relatedFiles, function ($relatedFile) use (&$body) {
            $body = str_replace($relatedFile, 'cid:' . sha1($relatedFile), $body);
            $this->mailSender->addRelateFile($relatedFile, sha1($relatedFile));
        });

        return $body;
    }

    /** Проверяет существует ли прикрепляемый файл и добавляет его к сообщению
     * @param string $attachedFiles
     */
    private function addAttachments($attachedFiles)
    {
        array_walk($attachedFiles, function ($attachment) {
            if (file_exists($attachment['path'])) {
                $this->mailSender->addAttachmentFile($attachment['path'], $attachment['name'] . '.' . $attachment['type']);
            }
            else {
                throw new NotificationSystemException("Файл '{$attachment['path']}', прикрепляемый к сообщению, не существует");
            }
        });
    }
}
