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

namespace AuroraExtensions\SimpleReturns\Controller\Rma;

use AuroraExtensions\SimpleReturns\{
    Helper\Action as ActionHelper,
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Model\ViewModel\Rma\ListView as ViewModel,
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

    /** @property OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @property PageFactory $resultPageFactory */
    protected $resultPageFactory;

    /** @property ViewModel $viewModel */
    protected $viewModel;

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
     * Execute returns_label_orders action.
     *
     * @return Page
     */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
