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
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Helper\Action as ActionHelper,
    Model\AdapterModel\Sales\Order as OrderAdapterModel,
    Model\ViewModel\Label as ViewModel,
    Shared\Action\Redirector,
    Shared\ModuleComponentInterface
};

use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpGetActionInterface,
    Controller\Result\Redirect as ResultRedirect,
    Data\Form\FormKey\Validator as FormKeyValidator,
    Exception\NoSuchEntityException,
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

    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

    /** @property OrderAdapterModel $orderAdapter */
    protected $orderAdapter;

    /** @property PageFactory $resultPageFactory */
    protected $resultPageFactory;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @property ViewModel $viewModel */
    protected $viewModel;

    /**
     * @param Context $context
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param PageFactory $resultPageFactory
     * @param OrderAdapterModel $orderAdapter
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param ViewModel $viewModel
     * @return void
     */
    public function __construct(
        Context $context,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        PageFactory $resultPageFactory,
        OrderAdapterModel $orderAdapter,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        ViewModel $viewModel
    ) {
        parent::__construct($context);
        $this->__initialize();
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderAdapter = $orderAdapter;
        $this->simpleReturnRepository = $simpleReturnRepository;
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
        $orderId = $request->getParam(self::PARAM_ORDER_ID);

        /** @var string|null $protectCode */
        $protectCode = $request->getParam(self::PARAM_PROTECT_CODE);

        if ($orderId !== null && $protectCode !== null) {
            /** @var OrderInterface[] $orders */
            $orders = $this->orderAdapter->getOrdersByIncrementIdAndProtectCode($orderId, $protectCode);

            if (!empty($orders)) {
                /** @var OrderInterface $order */
                $order = $orders[0];

                try {
                    /** @var SimpleReturnInterface $rma */
                    $rma = $this->simpleReturnRepository->get($order);

                    if ($rma && $rma->getId()) {
                        /** @var Magento\Framework\View\Element\AbstractBlock|bool $block */
                        $block = $resultPage->getLayout()->getBlock(self::BLOCK_RETURNS_LABEL_INDEX);

                        if ($block) {
                            $block->setData('view_model', $this->viewModel);
                        }

                        return $resultPage;
                    }

                    throw $this->exceptionFactory->create(
                        NoSuchEntityException::class,
                        __('Could not locate the requested RMA.')
                    );
                } catch (NoSuchEntityException $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

        return $this->getRedirect(self::ROUTE_RETURNS_LABEL_ORDERS);
    }
}
