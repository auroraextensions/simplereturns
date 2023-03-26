<?php
/**
 * Results.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Controller\Orders
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Orders;

use AuroraExtensions\SimpleReturns\Model\Adapter\Sales\Order as OrderAdapter;
use AuroraExtensions\SimpleReturns\Model\ViewModel\Rma\ListView as ViewModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

use function trim;

class Results extends Action implements HttpGetActionInterface
{
    private const BLOCK_ID = 'simplereturns_orders_results';
    private const DATA_PERSISTOR_KEY = 'simplereturns_data';

    /** @var DataPersistorInterface $dataPersistor */
    private $dataPersistor;

    /** @var OrderAdapter $orderAdapter */
    private $orderAdapter;

    /** @var PageFactory $resultPageFactory */
    private $resultPageFactory;

    /** @var ViewModel $viewModel */
    private $viewModel;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param OrderAdapter $orderAdapter
     * @param PageFactory $resultPageFactory
     * @param ViewModel $viewModel
     * @return void
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        OrderAdapter $orderAdapter,
        PageFactory $resultPageFactory,
        ViewModel $viewModel
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->orderAdapter = $orderAdapter;
        $this->resultPageFactory = $resultPageFactory;
        $this->viewModel = $viewModel;
    }

    /**
     * @return Page
     */
    public function execute()
    {
        /** @var array $data */
        $data = $this->dataPersistor->get(self::DATA_PERSISTOR_KEY);
        $this->dataPersistor->clear(self::DATA_PERSISTOR_KEY);

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var string|null $zipCode */
        $zipCode = $data['zip_code'] ?? null;
        $zipCode = !empty($zipCode) ? trim($zipCode) : $zipCode;

        /** @var string|null $email */
        $email = $data['email'] ?? null;
        $email = !empty($email) ? trim($email) : $email;

        /** @var string|null $orderId */
        $orderId = $data['order_id'] ?? null;
        $orderId = !empty($orderId) ? trim($orderId) : $orderId;

        if ($zipCode !== null) {
            /** @var OrderInterface[] $orders */
            $orders = [];

            if ($email !== null) {
                $orders = $this->orderAdapter
                    ->getOrdersByCustomerEmailAndZipCode($email, $zipCode);
            } elseif ($orderId !== null) {
                $orders = $this->orderAdapter
                    ->getOrdersByIncrementIdAndZipCode($orderId, $zipCode);
            }

            $this->viewModel->setData('orders', $orders);
        }

        /** @var AbstractBlock|bool $block */
        $block = $resultPage->getLayout()->getBlock(self::BLOCK_ID);

        if ($block) {
            $block->setData('view_model', $this->viewModel);
        }

        return $resultPage;
    }
}
