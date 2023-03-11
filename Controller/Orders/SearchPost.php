<?php
/**
 * SearchPost.php
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

use AuroraExtensions\ModuleComponents\Component\Http\Request\RedirectTrait;
use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Model\ViewModel\Rma\ListView as ViewModel,
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

use function __;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchPost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    use RedirectTrait;

    /** @var CustomerRepositoryInterface $customerRepository */
    protected $customerRepository;

    /** @var DataPersistorInterface $dataPersistor */
    protected $dataPersistor;

    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

    /** @var OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @var ViewModel $viewModel */
    protected $viewModel;

    /**
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataPersistorInterface $dataPersistor
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param OrderAdapter $orderAdapter
     * @param ViewModel $viewModel
     * @return void
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        DataPersistorInterface $dataPersistor,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        OrderAdapter $orderAdapter,
        ViewModel $viewModel
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->dataPersistor = $dataPersistor;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->orderAdapter = $orderAdapter;
        $this->viewModel = $viewModel;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        if ($request->isPost() && $this->formKeyValidator->validate($request)) {
            /** @var array|null $params */
            $params = $request->getPost('simplereturns');

            if ($params !== null) {
                /** @var string|null $email */
                $email = !empty($params['email']) ? $params['email'] : null;

                /** @var int|string|null $orderId */
                $orderId = !empty($params['order_id']) ? $params['order_id'] : null;

                /** @var int|string|null $zipCode */
                $zipCode = !empty($params['zip_code']) ? $params['zip_code'] : null;

                try {
                    /** @var LocalizedException $exception */
                    $exception = $this->exceptionFactory->create(
                        LocalizedException::class,
                        __(self::ERROR_MISSING_URL_PARAMS)
                    );

                    if ($zipCode === null) {
                        throw $exception;
                    }

                    /** @var array $data */
                    $data = [
                        'zip_code' => OrderAdapter::truncateZipCode($zipCode),
                        'is_checked' => true,
                    ];

                    if ($email !== null) {
                        $data['email'] = $email;
                    } elseif ($orderId !== null) {
                        $data['order_id'] = $orderId;
                    } else {
                        throw $exception;
                    }

                    $this->dataPersistor->set(self::DATA_PERSISTOR_KEY, $data);
                } catch (LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

        return $this->getRedirectToPath(self::ROUTE_SIMPLERETURNS_ORDERS_RESULTS);
    }
}
