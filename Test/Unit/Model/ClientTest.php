<?php


namespace SwedbankPay\Core\Test\Unit\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use SwedbankPay\Core\Exception\ClientException;
use SwedbankPay\Core\Helper\Config;
use SwedbankPay\Core\Logger\Logger;
use SwedbankPay\Core\Model\Client;

class ClientTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var Logger|MockObject
     */
    protected $logger;

    public function setUp()
    {
        $this->config = $this->createMock(Config::class);
        $this->logger = $this->createMock(Logger::class);
    }

    public function testConstructorDoesNotDoAnythingWhenModuleIsNotActive()
    {
        $this->config->expects($this->once())->method('isActive')->willReturn(false);
        $this->config->expects($this->never())->method('getValue')->with('merchant_token')->willReturn(null);
        $this->config->expects($this->never())->method('getValue')->with('payee_id')->willReturn(null);

        $this->logger->expects($this->never())->method('error');

        /** @var Client|MockObject $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invokeClientConstructor($client);
    }

    public function testConstructorThrowsExceptionForInvalidConfig()
    {
        $this->config->expects($this->once())->method('isActive')->willReturn(true);
        $this->config->expects($this->at(1))->method('getValue')->with('merchant_token')->willReturn(null);
        $this->config->expects($this->at(2))->method('getValue')->with('payee_id')->willReturn(null);

        $this->logger->expects($this->atLeastOnce())->method('error');

        /** @var Client|MockObject $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        //phpcs:ignore
        $client->expects($this->once())->method('getCurlHandler')->willReturn(curl_init());
        $client->expects($this->once())->method('__toArray')->willReturn([]);
        $client->expects($this->never())->method('setAccessToken');
        $client->expects($this->never())->method('setPayeeId');

        $this->expectException(ClientException::class);

        $this->invokeClientConstructor($client);
    }

    public function testConstructor()
    {
        $this->config->expects($this->once())->method('isActive')->willReturn(true);
        $this->config->expects($this->at(1))->method('getValue')->with('merchant_token')->willReturn('xxx');
        $this->config->expects($this->at(2))->method('getValue')->with('payee_id')->willReturn('yyy');

        $this->logger->expects($this->never())->method('error');

        /** @var Client|MockObject $client */
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        //phpcs:ignore
        $client->expects($this->once())->method('getCurlHandler')->willReturn(curl_init());
        $client->expects($this->once())->method('__toArray')->willReturn([]);
        $client->expects($this->once())->method('setAccessToken');
        $client->expects($this->once())->method('setPayeeId');
        $client->expects($this->atLeastOnce())->method('setMode');

        $this->invokeClientConstructor($client);
    }

    protected function invokeClientConstructor($client)
    {
        $reflectedClass = new ReflectionClass(Client::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($client, $this->config, $this->logger);
    }
}
