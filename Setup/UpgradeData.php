<?php

namespace SwedbankPay\Core\Setup;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\Status;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

/**
 * Class UpgradeData
 * Creates custom order statuses
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Status
     */
    protected $statusResourceModel;

    /**
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * @var CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * UpgradeData constructor.
     * @param Status $statusResourceModel
     * @param StatusFactory $statusFactory
     * @param CollectionFactory $statusCollectionFactory
     */
    public function __construct(
        Status $statusResourceModel,
        StatusFactory $statusFactory,
        CollectionFactory $statusCollectionFactory
    ) {
        $this->statusFactory = $statusFactory;
        $this->statusResourceModel = $statusResourceModel;
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * Upgrades DB for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws AlreadyExistsException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createCustomStatuses();

        $setup->endSetup();
    }

    /**
     * @throws AlreadyExistsException
     * @throws Exception
     */
    protected function createCustomStatuses()
    {
        $existingStatuses = $this->getExistingStatuses();

        foreach ($this->getStatuses() as $status => $label) {
            if (!array_key_exists($status, $existingStatuses)) {
                /** @var \Magento\Sales\Model\Order\Status $status */
                $newStatus = $this->statusFactory->create();

                $newStatus->setData(['label' => $label, 'status' => $status]);

                $this->statusResourceModel->save($newStatus);
            }
        }
    }

    /**
     * Get custom order statuses
     *
     * @return array
     */
    protected function getStatuses()
    {
        return [
            'swedbank_pay_pending' => __('SwedbankPay Pending'),
            'swedbank_pay_reversed' => __('SwedbankPay Reversed'),
            'swedbank_pay_cancelled_reversal'  => __('SwedbankPay Cancelled Reversal'),
        ];
    }

    /**
     * Get existing order statuses
     *
     * @return array
     */
    public function getExistingStatuses()
    {
        return $this->statusCollectionFactory->create()->getItems();
    }
}
