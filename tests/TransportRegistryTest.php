<?php

namespace NotificationSystem;

use NotificationSystem\Transports\EmailTransport;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class TransportRegistryTest extends TestCase
{
    protected function makeEmptyTransportRegistry()
    {
        $transportRegistry = new TransportRegistry();

        return $transportRegistry;
    }

    public function testGetTransport_TransportExists_returnTransportObject()
    {
        // Arrange
        $transportRegistry = $this->makeEmptyTransportRegistry();
        $transportRegistry->registerTransport(
            100, EmailTransport::class, 'Transport'
        );

        // Act
        $result = $transportRegistry->makeTransportByCode(100);

        // Assert
        $this->assertInstanceOf(EmailTransport::class, $result);
    }

    public function testGetTransport_TransportNotExists_returnNull()
    {
        // Assert
        $this->expectExceptionMessageMatches(
            '/^Transport with code \[\d*\] is not registered\.$/'
        );

        // Arrange
        $transportRegistry = $this->makeEmptyTransportRegistry();

        // Act
        $transportRegistry->makeTransportByCode(100);
    }
}