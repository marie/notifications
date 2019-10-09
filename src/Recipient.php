<?php

namespace NotificationSystem;

interface Recipient
{
    public function getId();
    public function getGroupId();
    public function getClientId();
    public function getEmail();
    public function getMobilePhone();
}