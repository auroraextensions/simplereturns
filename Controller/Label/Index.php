<?php
/**
 * Index.php
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
    Model\Label\Processor,
    Model\Label as LabelModel,
    Model\Orders as OrdersModel,
    Shared\Action\Redirector,
    Shared\ModuleComponentInterface,
    ViewModel\Label as ViewModel
};

use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpGetActionInterface,
    Controller\Result\Redirect as ResultRedirect,
    Data\Form\FormKey\Validator as FormKeyValidator,
    View\Result\PageFactory
};

class Index extends Action implements
    HttpGetActionInterface,
    ModuleComponentInterface
{
    /** @see AuroraExtensions\SimpleReturns\Shared\Action\Redirector */
    use Redirector {
        Redirector::__initialize as protected;
    }

    /** @property FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

    /** @property LabelModel $labelModel */
    protected $labelModel;

    /** @property OrdersModel $ordersModel */
    protected $ordersModel;

    /** @property Processor $processor */
    protected $processor;

    /** @property PageFactory $resultPageFactory */
    protected $resultPageFactory;

    /** @property ViewModel $viewModel */
    protected $viewModel;

    /**
     * @param Context $context
     * @param FormKeyValidator $formKeyValidator
     * @param LabelModel $labelModel
     * @param PageFactory $resultPageFactory
     * @param OrdersModel $ordersModel
     * @param Processor $processor
     * @param ViewModel $viewModel
     * @return void
     */
    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        LabelModel $labelModel,
        PageFactory $resultPageFactory,
        OrdersModel $ordersModel,
        Processor $processor,
        ViewModel $viewModel
    ) {
        parent::__construct($context);
        $this->__initialize();
        $this->formKeyValidator = $formKeyValidator;
        $this->labelModel = $labelModel;
        $this->resultPageFactory = $resultPageFactory;
        $this->ordersModel = $ordersModel;
        $this->processor = $processor;
        $this->viewModel = $viewModel;
    }

    /**
     * Execute returns_label_index action.
     *
     * @return Redirect|Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        /** @var string|null $orderId */
        $orderId = $request->getParam(self::PARAM_ORDER_ID, null);

        /** @var string|null $protectCode */
        $protectCode = $request->getParam(self::PARAM_PROTECT_CODE, null);

        if ($orderId !== null && $protectCode !== null) {
            /** @var OrderInterface[] $orders */
            $orders = $this->ordersModel->getOrdersByIncrementIdAndProtectCode($orderId, $protectCode);

            if (!empty($orders)) {
                /** @var OrderInterface $order */
                $order = $orders[0];

                /* View model requires order for cache lookup. */
                $this->viewModel->setOrder($order);

                /** @var string $cacheKey */
                $cacheKey = $this->labelModel->getCacheKey($order);

                if (($this->labelModel->isCacheEnabled() && $this->labelModel->hasCachedImage($cacheKey))
                    || $this->processor->requestReturnLabel($order)
                ) {
                    /** @var Magento\Framework\View\Element\AbstractBlock|bool $block */
                    $block = $resultPage->getLayout()->getBlock(self::BLOCK_RETURNS_LABEL_INDEX);

                    if ($block) {
                        $block->setData('view_model', $this->viewModel);
                    }

                    return $resultPage;
                }
            }
        }

        return $this->getRedirect(self::ROUTE_RETURNS_LABEL_ORDERS);
    }
}
