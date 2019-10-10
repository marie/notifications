<?php

namespace NotificationSystem;

/**
 * Хранит список разрешённых шаблонов
 * @package NotificationSystem
 */
class TemplateRegistry
{
    /**
     * Массив со списком доступных уведомлений и соотвествующих им шаблонов
     * @var array
     */
    protected $registry = [];

    /**
     * @var array
     */
    protected $codesMap = [];

    /**
     * @var array
     */
    protected $objectsCache = [];

    /**
     * Получить шаблон по коду
     * @param string $templateCode
     * @return BaseTemplate
     * @throws NotificationSystemException
     */
    public function getTemplateByCode($templateCode)
    {
        $templateClass = $this->getTemplateClassByCode($templateCode);

        if (!array_key_exists($templateClass, $this->registry)) {
            throw new NotificationSystemException("The template [$templateClass] doesn't exist.");
        }

        if (!array_key_exists($templateClass, $this->objectsCache)) {
            $this->objectsCache[$templateClass] = new $templateClass;
        }

        return $this->objectsCache[$templateClass];
    }

    /**
     * Возвращает полное имя класса по идентификатору
     * @param int $code
     * @return BaseTemplate
     * @throws NotificationSystemException
     */
    public function getTemplateClassByCode($code)
    {
        if (!isset($this->codesMap[$code])) {
            throw new NotificationSystemException(sprintf('Template with code [%d] is not registered.', $code));
        }

        return $this->codesMap[$code];
    }

    /** Возвращает все доступные для уведомления шаблоны
     * @param $notificationCode
     * @return mixed
     */
    public function getAllTemplatesForNotification($notificationCode)
    {
        $templates = $this->getTemplateList();

        foreach ($templates as $code => $templateDescription) {
            $notification = NotificationCodes::getNotificationClassByCode($notificationCode);

            $templateName = $this->getTemplateClassByCode($code);
            if ($templateName::isAvailableForNotificationByName($notification)) {
                $result[$code] = $templateDescription;
            }
        }

        return $result;
    }

    /**
     * Список Шаблонов в виде [code => "Описание Шаблона"]
     * @return array
     */
    public function getTemplateList()
    {
        $result = [];

        foreach ($this->codesMap as $code => $className) {
            $result[$code] = $this->registry[$className];
        }

        return $result;
    }

    /**
     * Устанавливает начальные данные (уведомления и названия уведомлений)
     * круглые числа (например 10, 20 - для основных шаблонов уведомлений
     * промежуточные, например 11, 12 - для пользовательских шаблонов уведомлений
     */
    public function setupDefaultTemplates()
    {
        $this->registerTemplate(
            10,
            RestorePasswordTemplate::class,
            'Уведомление для восстановления пароля'
        );
        $this->registerTemplate(
            11,
            RestorePasswordSpringTemplate::class,
            'Уведомление для восстановления пароля (Spring)'
        );
        $this->registerTemplate(
            20,
            Notifications\ChangePasswordNotification\ChangePasswordTemplate::class,
            'Уведомление о смене пароля'
        );
        $this->registerTemplate(
            21,
            Notifications\ChangePasswordNotification\ChangePasswordSpringTemplate::class,
            'Уведомление о смене пароля (Spring)'
        );
        $this->registerTemplate(
            31,
            OrderCreatedSpringTemplate::class,
            'Уведомление о создании заказа (Spring) EN'
        );
        $this->registerTemplate(
            41,
            TravellerChoseApproverSpringTemplate::class,
            'Уведомление о начале согласования (Spring) для апрувера EN'
        );
        $this->registerTemplate(
            51,
            ApproverConfirmedOrderSpringTemplate::class,
            'Уведомление о согласовании (Spring) EN'
        );
        $this->registerTemplate(
            61,
            ApproverRejectedOrderSpringTemplate::class,
            'Уведомление о несогласовании (Spring) EN'
        );
    }


    /**
     * По входящим данным формирует массивы с информацией о шаблонах
     * @param int $code
     * @param string $template
     * @param string $description
     */
    public function registerTemplate($code, $template, $description)
    {
        $this->codesMap[$code] = $template;
        $this->registry[$template] = $description;
    }
}
