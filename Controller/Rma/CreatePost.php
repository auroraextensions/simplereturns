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
    Api\Data\SimpleReturnInterface,
    Api\Data\SimpleReturnInterfaceFactory,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Shared\Action\Redirector,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpPostActionInterface,
    Controller\Result\Redirect as ResultRedirect,
    Data\Form\FormKey\Validator as FormKeyValidator,
    Exception\LocalizedException,
    Exception\NoSuchEntityException
};

class CreatePost extends Action implements
    HttpPostActionInterface,
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

    /** @property OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @property SimpleReturnInterfaceFactory $simpleReturnFactory */
    protected $simpleReturnFactory;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /**
     * @param Context $context
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param OrderAdapter $orderAdapter
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @return void
     */
    public function __construct(
        Context $context,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        OrderAdapter $orderAdapter,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository
    ) {
        parent::__construct($context);
        $this->__initialize();
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->orderAdapter = $orderAdapter;
        $this->simpleReturnFactory = $simpleReturnFactory;
        $this->simpleReturnRepository = $simpleReturnRepository;
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

            /** @var string $protectCode */
            $protectCode = $params['protect_code'] ?? null;
            $protectCode = !empty($protectCode) ? $protectCode : null;

            /** @var string $reason */
            $reason = $params['reason'] ?? null;
            $reason = !empty($reason) ? $reason : null;

            /** @var string $resolution */
            $resolution = $params['resolution'] ?? null;
            $resolution = !empty($resolution) ? $resolution : null;

            /** @var string $comments */
            $comments = $params['comments'] ?? null;
            $comments = !empty($comments) ? $comments : null;

            /** @var array $fields */
            $fields = [
                self::FIELD_INCREMENT_ID => $orderId,
                self::FIELD_PROTECT_CODE => $protectCode,
            ];

            try {
                /** @var OrderInterface[] $orders */
                $orders = $this->orderAdapter->getOrdersByFields($fields);

                if (!empty($orders)) {
                    /** @var OrderInterface $order */
                    $order = $orders[0];

                    try {
                        /** @var SimpleReturn $rma */
                        $rma = $this->simpleReturnRepository->get($order);

                        /** @note Consider possible redirect to RMA view page. */
                        if ($rma->getId()) {
                            /** @var LocalizedException $exception */
                            $exception = $this->exceptionFactory->create(
                                LocalizedException::class,
                                __('An RMA request already exists for this order.')
                            );

                            throw $exception;
                        }
                    } catch (LocalizedException $e) {
                        throw $e;
                    } catch (NoSuchEntityException $e) {
                        /* RMA doesn't exist, so continue processing. */
                    }

                    /** @var SimpleReturn $rma */
                    $rma = $this->simpleReturnFactory->create();

                    /** @var array $data */
                    $data = [
                        'order_id'   => $orderId,
                        'reason'     => $reason,
                        'resolution' => $resolution,
                        'comments'   => $comments,
                    ];

                    $this->simpleReturnRepository->save(
                        $rma->addData($data)
                    );
                }

                /** @var LocalizedException $exception */
                $exception = $this->exceptionFactory->create(
                    LocalizedException::class,
                    __('Unable to create RMA request.')
                );

                throw $exception;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError($e->getMessage());
            }


            /**
             * @todo: Check for existing RMA, redirect if exists.
             *        If no RMA exists, create new SimpleReturn RMA.
             */
        }

        return $this->getRedirectToPath(self::ROUTE_SIMPLERETURNS_ORDERS_SEARCH);
    }
}
