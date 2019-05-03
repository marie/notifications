<?php

namespace NotificationSystem;

/**
 * Класс для хранения информации об одном сообщении
 *
 * @package NotificationSystem
 */
class Message
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $attachments;

    /**
     * @param string $title
     * @param string $body
     * @param array $attachments
     */
    public function __construct($title, $body, $attachments = [])
    {
        $this->title       = $title;
        $this->body        = $body;
        $this->attachments = $attachments;
    }

    /**
     * @param array $attachment
     */
    public function addAttachment($attachment)
    {
        $this->attachments[] = $attachment;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getAttachments()
    {
        return $this->attachments;
    }
}
