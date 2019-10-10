<?php

namespace NotificationSystem;

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

        $transport = $this->prophesize(Transport::class)->reveal();

        $transportName = get_class($transport);

        $transportRegistry->registerTransport(
            100, $transportName, 'Transport'
        );

        // Act
        $result = $transportRegistry->getTransportByCode(100);

        // Assert
        $this->assertInstanceOf($transportName, $result);
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
        $transportRegistry->getTransportByCode(100);
    }
}