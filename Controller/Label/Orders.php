<?php
/**
 * Orders.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Label;

use AuroraExtensions\SimpleReturns\{
    Helper\Action as ActionHelper,
    Model\AdapterModel\Sales\Order as OrdersModel,
    Model\ViewModel\Orders as ViewModel,
    Shared\ModuleComponentInterface
};

use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpGetActionInterface,
    App\Request\DataPersistorInterface,
    View\Result\PageFactory
};

class Orders extends Action implements
    HttpGetActionInterface,
    ModuleComponentInterface
{
    /** @property DataPersistorInterface $dataPersistor */
    protected $dataPersistor;

    /** @property OrdersModel $ordersModel */
    protected $ordersModel;

    /** @property PageFactory $resultPageFactory */
    protected $resultPageFactory;

    /** @property ViewModel $viewModel */
    protected $viewModel;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param OrdersModel $ordersModel
     * @param PageFactory $resultPageFactory
     * @param ViewModel $viewModel
     * @return void
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        OrdersModel $ordersModel,
        PageFactory $resultPageFactory,
        ViewModel $viewModel
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->ordersModel = $ordersModel;
        $this->resultPageFactory = $resultPageFactory;
        $this->viewModel = $viewModel;
    }

    /**
     * Execute returns_label_orders action.
     *
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var array $data */
        $data = $this->dataPersistor->get(self::DATA_PERSISTOR_KEY);
        $this->dataPersistor->clear(self::DATA_PERSISTOR_KEY);

        if ($data) {
            $this->viewModel->setData($data);

            /** @var string|null $email */
            $email = $data[self::PARAM_EMAIL] ?? null;

            /** @var string|null $orderId */
            $orderId = $data[self::PARAM_ORDER_ID] ?? null;

            /** @var string|null $zipCode */
            $zipCode = $data[self::PARAM_ZIP_CODE] ?? null;

            if ($email !== null && $zipCode !== null) {
                /** @var array $orders */
                $orders = $this->ordersModel->getOrdersByCustomerEmailAndZipCode($email, $zipCode);

                $this->viewModel->setData('orders', $orders);
            } elseif ($orderId !== null && $zipCode !== null) {
                /** @var array $orders */
                $orders = $this->ordersModel->getOrdersByIncrementIdAndZipCode($orderId, $zipCode);

                $this->viewModel->setData('orders', $orders);
            }

            /** @var Magento\Framework\View\Element\AbstractBlock|bool $block */
            $block = $resultPage->getLayout()->getBlock(self::BLOCK_RETURNS_LABEL_ORDERS);

            if ($block) {
                $block->setData('view_model', $this->viewModel);
            }
        }

        return $resultPage;
    }
}
