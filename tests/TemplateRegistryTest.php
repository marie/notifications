<?php

namespace NotificationSystem;

use NotificationSystem\Templates\BaseTemplate;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class TemplateRegistryTest extends TestCase
{
    public function testGetTemplate_TemplateExists_returnTemplateObject()
    {
        // Arrange
        $templateRegistry = new TemplateRegistry();

        $template = $this->prophesize(BaseTemplate::class)->reveal();
        $templateName = get_class($template);

        $templateRegistry->registerTemplate(
            100,
            $templateName,
            'Password was changed'
        );

        // Act
        $result = $templateRegistry->getTemplateByCode(100);

        // Assert
        $this->assertInstanceOf(BaseTemplate::class, $result);
    }

    public function testGetTemplate_TemplateNotExists_returnNull()
    {
        // Assert
        $this->expectExceptionMessageMatches(
            '/^Template with code \[\d*\] is not registered\.$/'
        );

        // Arrange
        $templateRegistry = new TemplateRegistry();

        // Act
        $templateRegistry->getTemplateByCode(100);
    }
}