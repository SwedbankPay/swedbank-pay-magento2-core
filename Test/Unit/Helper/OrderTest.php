<?php


namespace SwedbankPay\Core\Test\Unit\Helper;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Service\InvoiceService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SwedbankPay\Core\Helper\Order as OrderHelper;

class OrderTest extends TestCase
{
    /**
     * @var OrderRepositoryInterface|MockObject
     */
    protected $orderRepository;

    /**
     * @var OrderManagementInterface|MockObject
     */
    protected $orderManagement;

    /**
     * @var InvoiceRepositoryInterface|MockObject
     */
    protected $invoiceRepository;

    /**
     * @var InvoiceService|InvoiceManagementInterface|MockObject
     */
    protected $invoiceManagement;

    /**
     * @var InvoiceSender|MockObject
     */
    protected $invoiceSender;

    /**
     * @var OrderHelper
     */
    protected $orderHelper;

    public function setUp()
    {
        $this->orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->orderManagement = $this->getMockBuilder(OrderManagementInterface::class)
            ->setMethods(['cancel'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceRepository = $this->getMockBuilder(InvoiceRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceManagement = $this->getMockBuilder(InvoiceManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceSender = $this->getMockBuilder(InvoiceSender::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderHelper = new OrderHelper(
            $this->orderRepository,
            $this->orderManagement,
            $this->invoiceRepository,
            $this->invoiceManagement,
            $this->invoiceSender
        );
    }

    public function testCancelOrderWithNonCancelableOrder()
    {
        /** @var OrderInterface|MockObject $order */
        $order = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['canCancel'])
            ->getMock();

        $order->expects($this->once())
            ->method('canCancel')
            ->willReturn(false);

        $this->orderManagement
            ->expects($this->never())
            ->method('cancel');

        $this->orderHelper->cancelOrder($order);
    }

    public function testCancelOrder()
    {
        $orderId = 1;
        $comment = 'Order was cancelled';

        /** @var OrderInterface|MockObject $order */
        $order = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getStatus', 'canCancel', 'addStatusToHistory'])
            ->getMock();

        $order->expects($this->once())->method('canCancel')->willReturn(true);
        $order->expects($this->once())->method('getId')->willReturn($orderId);
        $order->expects($this->once())->method('addStatusToHistory');

        $this->orderManagement->expects($this->once())->method('cancel')->with($orderId);
        $this->orderRepository->expects($this->once())->method('save')->with($order);

        $this->orderHelper->cancelOrder($order, $comment);
    }
}
