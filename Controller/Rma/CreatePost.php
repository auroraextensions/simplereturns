<?php
/**
 * CreatePost.php
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
    Exception\ExceptionFactory,
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Shared\Action\Redirector,
    Shared\ModuleComponentInterface
};
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpPostActionInterface,
    App\Request\DataPersistorInterface,
    Controller\Result\Redirect as ResultRedirect,
    Data\Form\FormKey\Validator as FormKeyValidator,
    Exception\LocalizedException
};

class CreatePost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    /** @see AuroraExtensions\SimpleReturns\Shared\Action\Redirector */
    use Redirector {
        Redirector::__initialize as protected;
    }

    /** @property CustomerRepositoryInterface $customerRepository */
    protected $customerRepository;

    /** @property DataPersistorInterface $dataPersistor */
    protected $dataPersistor;

    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

    /** @property OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /**
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataPersistorInterface $dataPersistor
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param OrderAdapter $orderAdapter
     * @return void
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        DataPersistorInterface $dataPersistor,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        OrderAdapter $orderAdapter
    ) {
        parent::__construct($context);
        $this->__initialize();
        $this->customerRepository = $customerRepository;
        $this->dataPersistor = $dataPersistor;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->orderAdapter = $orderAdapter;
    }

    /**
     * Execute returns_label_ordersPost POST action.
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        if (!$request->isPost() || !$this->formKeyValidator->validate($request)) {
            return $this->getRedirectToPath(self::ROUTE_SALES_GUEST_VIEW);
        }

        /** @var array|null $params */
        $params = $request->getPost('simplereturns');

        if ($params !== null) {
            /** @var int|string $orderId */
            $orderId = $params['order_id'] ?? null;
            $orderId = !empty($orderId) ? $orderId : null;

            /**
             * @todo: Check for existing RMA, redirect if exists.
             *        If no RMA exists, create new SimpleReturn RMA.
             */
        }

        return $this->getRedirectToPath(self::ROUTE_SIMPLERETURNS_ORDERS_SEARCH);
    }
}
