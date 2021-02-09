<?php


namespace SwedbankPay\Core\Test\Unit\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SwedbankPay\Api\Service\Data\RequestInterface;
use SwedbankPay\Core\Exception\ServiceException;
use SwedbankPay\Core\Logger\Logger;
use SwedbankPay\Core\Model\Client;
use SwedbankPay\Core\Model\Service;

class ServiceTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    protected $client;

    /**
     * @var Logger|MockObject
     */
    protected $logger;

    /**
     * @var Service
     */
    protected $service;

    public function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = new Service($this->client, $this->logger);
    }

    /**
     * @throws ServiceException
     */
    public function testInitWithoutServiceAndOperation()
    {
        $this->logger
            ->expects($this->exactly(2))
            ->method('error');

        $this->expectException(ServiceException::class);
        $this->service->init();
    }

    /**
     * @throws ServiceException
     */
    public function testInitWithoutOperation()
    {
        $this->logger
            ->expects($this->once())
            ->method('error');

        $this->expectException(ServiceException::class);
        $this->service->init('creditcard');
    }

    /**
     * @throws ServiceException
     */
    public function testInitWithWrongClass()
    {
        $this->logger
            ->expects($this->once())
            ->method('error');

        $this->expectException(ServiceException::class);
        $this->service->init('creditcard', 'purchase2');
    }

    /**
     * @throws ServiceException
     */
    public function testInit()
    {
        $this->logger
            ->expects($this->never())
            ->method('error');

        $request = $this->service->init('creditcard', 'purchase');

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertInstanceOf(Client::class, $request->getClient());
    }
}
