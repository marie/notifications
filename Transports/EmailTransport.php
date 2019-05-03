<?php

namespace NotificationSystem\Transports;

use NotificationSystem\NotificationSystemException;
use NotificationSystem\Message;
use NotificationSystem\Transport;

class EmailTransport implements Transport
{
    /**
     * Адрес, куда транспорт будет отправлять сообщение
     * @var string
     */
    protected $address;

    /**
     * @var MimeMessage
     */
    protected $mimeMessage;

    /**
     * @var string
     */
    private $nameFrom = 'site';

    /**
     * @var string
     */
    private $emailFrom = 'no-reply@site';

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

        $this->mimeMessage = new MimeMessage();

        $this->mimeMessage->setFrom($this->nameFrom, $this->emailFrom);
        $this->mimeMessage->setTo($this->address);

        $this->mimeMessage->setContentType('html');
        $this->mimeMessage->setSubject($message->getTitle());

        // находим все картинки внутри сообщения <img src="">
        $relatedFiles = $this->findRelatedImages($body);

        // и заменяем их на cid:imageIdentificator для того чтобы потом они корректно отображались в письме
        $body = $this->prepareRelatedImages($body, $relatedFiles);

        // проходим циклом по всем прикрепляемым файлам, и если файлы существуют, то добавляем их к сообщению
        $attachedFiles = $message->getAttachments();
        $this->addAttachments($attachedFiles);

        // тело сообщения добавляем к отправляемому сообщению
        $this->mimeMessage->setContent($body);

        $this->mimeMessage->send();
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
     * @param Profile $profile
     * @return void
     */
    public function setAddressFromProfile(Profile $profile)
    {
        $address = trim($profile->profileEmails()->getPriorityEmailAddress());

        if (empty($address)) {
            $address = trim($profile->email);
        }

        $this->address = $address;
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
            $this->mimeMessage->addRelateFile($relatedFile, sha1($relatedFile));
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
                $this->mimeMessage->addAttachmentFile($attachment['path'], $attachment['name'] . '.' . $attachment['type']);
            }
            else {
                throw new NotificationSystemException("Файл '{$attachment['path']}', прикрепляемый к сообщению, не существует");
            }
        });
    }
}
