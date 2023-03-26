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
use AuroraExtensions\SimpleReturns\Model\Adapter\Sales\Order as OrderAdapter;
use AuroraExtensions\SimpleReturns\Model\ViewModel\Rma\ListView as ViewModel;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Throwable;

use function __;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SearchPost extends Action implements HttpPostActionInterface
{
    /**
     * @method Redirect getRedirect()
     * @method Redirect getRedirectToPath()
     * @method Redirect getRedirectToUrl()
     */
    use RedirectTrait;

    private const DATA_PERSISTOR_KEY = 'simplereturns_data';
    private const ROUTE_PATH = 'simplereturns/orders/results';

    /** @var CustomerRepositoryInterface $customerRepository */
    private $customerRepository;

    /** @var DataPersistorInterface $dataPersistor */
    private $dataPersistor;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var FormKeyValidator $formKeyValidator */
    private $formKeyValidator;

    /** @var OrderAdapter $orderAdapter */
    private $orderAdapter;

    /** @var ViewModel $viewModel */
    private $viewModel;

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
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        if ($request->isPost() && $this->formKeyValidator->validate($request)) {
            /** @var array|null $params */
            $params = $request->getPost('simplereturns');

            if ($params !== null) {
                /** @var string|null $email */
                $email = !empty($params['email'])
                    ? $params['email'] : null;

                /** @var int|string|null $orderId */
                $orderId = !empty($params['order_id'])
                    ? $params['order_id'] : null;

                /** @var int|string|null $zipCode */
                $zipCode = !empty($params['zip_code'])
                    ? $params['zip_code'] : null;

                try {
                    /** @var LocalizedException $exception */
                    $exception = $this->exceptionFactory->create(
                        LocalizedException::class,
                        __('Please provide an email or order ID and billing/shipping zip code.')
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

                    $this->dataPersistor->set(
                        self::DATA_PERSISTOR_KEY,
                        $data
                    );
                } catch (Throwable $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }

        return $this->getRedirectToPath(self::ROUTE_PATH);
    }
}
