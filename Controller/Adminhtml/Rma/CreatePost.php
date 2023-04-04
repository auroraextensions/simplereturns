<?php
/**
 * CreatePost.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Controller\Adminhtml\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Adminhtml\Rma;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\ModuleComponents\Model\Security\HashContext;
use AuroraExtensions\ModuleComponents\Model\Security\HashContextFactory;
use AuroraExtensions\ModuleComponents\Reflection\EventListener\ObservableEvent;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Component\System\ModuleConfigTrait;
use AuroraExtensions\SimpleReturns\Csi\System\Module\ConfigInterface;
use AuroraExtensions\SimpleReturns\Model\Adapter\Sales\Order as OrderAdapter;
use AuroraExtensions\SimpleReturns\Model\Display\LabelManager;
use AuroraExtensions\SimpleReturns\Model\Email\Transport\Customer as EmailTransport;
use DateTime;
use DateTimeFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Throwable;

use function __;
use function array_shift;
use function Ramsey\Uuid\v4;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends Action implements HttpPostActionInterface
{
    /**
     * @var ConfigInterface $moduleConfig
     * @method ConfigInterface getConfig()
     */
    use ModuleConfigTrait;

    private const FIELD_INCREMENT_ID = 'increment_id';
    private const FIELD_PROTECT_CODE = 'protect_code';
    private const PARAM_ORDER_ID = 'order_id';
    private const PARAM_PROTECT_CODE = 'code';

    /** @var DateTimeFactory $dateTimeFactory */
    private $dateTimeFactory;

    /** @var EmailTransport $emailTransport */
    private $emailTransport;

    /** @var Escaper $escaper */
    private $escaper;

    /** @var EventManagerInterface $eventManager */
    private $eventManager;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var FormKeyValidator $formKeyValidator */
    private $formKeyValidator;

    /** @var HashContextFactory $hashContextFactory */
    private $hashContextFactory;

    /** @var LabelManager $labelManager */
    private $labelManager;

    /** @var OrderAdapter $orderAdapter */
    private $orderAdapter;

    /** @var RemoteAddress $remoteAddress */
    private $remoteAddress;

    /** @var ResultJsonFactory $resultJsonFactory */
    private $resultJsonFactory;

    /** @var SimpleReturnInterfaceFactory $simpleReturnFactory */
    private $simpleReturnFactory;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /** @var StoreManagerInterface $storeManager */
    private $storeManager;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param DateTimeFactory $dateTimeFactory
     * @param EmailTransport $emailTransport
     * @param Escaper $escaper
     * @param EventManagerInterface $eventManager
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param HashContextFactory $hashContextFactory
     * @param LabelManager $labelManager
     * @param ConfigInterface $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param RemoteAddress $remoteAddress
     * @param ResultJsonFactory $resultJsonFactory
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        DateTimeFactory $dateTimeFactory,
        EmailTransport $emailTransport,
        Escaper $escaper,
        EventManagerInterface $eventManager,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        HashContextFactory $hashContextFactory,
        LabelManager $labelManager,
        ConfigInterface $moduleConfig,
        OrderAdapter $orderAdapter,
        RemoteAddress $remoteAddress,
        ResultJsonFactory $resultJsonFactory,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->dateTimeFactory = $dateTimeFactory;
        $this->emailTransport = $emailTransport;
        $this->escaper = $escaper;
        $this->eventManager = $eventManager;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->hashContextFactory = $hashContextFactory;
        $this->labelManager = $labelManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
        $this->remoteAddress = $remoteAddress;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->simpleReturnFactory = $simpleReturnFactory;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return ResultJson
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    #[ObservableEvent('simplereturns_adminhtml_rma_create_save_before')]
    #[ObservableEvent('simplereturns_adminhtml_rma_create_save_after')]
    public function execute()
    {
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        /** @var ResultJson $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        if (!$request->isPost()) {
            return $resultJson->setData([
                'errors' => true,
                'message' => __('Invalid request type. Must be POST request.'),
            ]);
        }

        if (!$this->formKeyValidator->validate($request)) {
            return $resultJson->setData([
                'errors' => true,
                'message' => __('Invalid form key.'),
            ]);
        }

        /** @var int|string|null $orderId */
        $orderId = $request->getParam(self::PARAM_ORDER_ID);
        $orderId = !empty($orderId) ? $orderId : null;

        /** @var string|null $protectCode */
        $protectCode = $request->getParam(self::PARAM_PROTECT_CODE);
        $protectCode = !empty($protectCode) ? $protectCode : null;

        /** @var string|null $status */
        $status = $request->getPostValue('status');
        $status = !empty($status)
            ? $this->escaper->escapeHtml($status) : null;

        /** @var string|null $reason */
        $reason = $request->getPostValue('reason');
        $reason = !empty($reason)
            ? $this->escaper->escapeHtml($reason) : null;

        /** @var string|null $resolution */
        $resolution = $request->getPostValue('resolution');
        $resolution = !empty($resolution)
            ? $this->escaper->escapeHtml($resolution) : null;

        /** @var string|null $comments */
        $comments = $request->getPostValue('comments');
        $comments = !empty($comments)
            ? $this->escaper->escapeHtml($comments) : null;

        /** @var bool $notifyCustomer */
        $notifyCustomer = $request->getPostValue('notify_customer');
        $notifyCustomer = $notifyCustomer !== null
            ? (bool) $notifyCustomer : false;

        /** @var array $fields */
        $fields = [
            self::FIELD_INCREMENT_ID => $orderId,
            self::FIELD_PROTECT_CODE => $protectCode,
        ];

        try {
            /** @var OrderInterface[] $orders */
            $orders = $this->orderAdapter
                ->getOrdersByFields($fields);

            if (empty($orders)) {
                /** @var LocalizedException $exception */
                $exception = $this->exceptionFactory->create(
                    LocalizedException::class,
                    __('Unable to create RMA request.')
                );
                throw $exception;
            }
        } catch (Throwable $e) {
            return $resultJson->setData([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }

        /** @var OrderInterface $order */
        $order = array_shift($orders);

        try {
            /** @var SimpleReturnInterface $rma */
            $rma = $this->simpleReturnRepository->get($order);

            if ($rma->getId()) {
                /** @var AlreadyExistsException $exception */
                $exception = $this->exceptionFactory->create(
                    AlreadyExistsException::class,
                    __('An RMA request already exists for this order.')
                );
                throw $exception;
            }
        /* RMA doesn't exist, continue processing. */
        } catch (NoSuchEntityException $e) {
            /** @var SimpleReturnInterface $rma */
            $rma = $this->simpleReturnFactory->create();

            /** @var string $remoteIp */
            $remoteIp = $this->remoteAddress->getRemoteAddress();

            /** @var int $storeId */
            $storeId = (int) $this->storeManager
                ->getStore()
                ->getId();

            /** @var HashContext $hashContext */
            $hashContext = $this->hashContextFactory->create([
                'data' => null,
                'algo' => 'crc32b',
            ]);

            /** @var string $token */
            $token = (string) $hashContext;

            /** @var DateTime $dateTime */
            $dateTime = $this->dateTimeFactory->create();

            /** @var array $data */
            $data = [
                'uuid' => v4(),
                'store_id' => $storeId,
                'order_id' => $orderId,
                'status' => $status,
                'reason' => $reason,
                'resolution' => $resolution,
                'comments' => $comments,
                'remote_ip' => $remoteIp,
                'token' => $token,
                'created_at' => $dateTime,
            ];

            $this->eventManager->dispatch(
                'simplereturns_adminhtml_rma_create_save_before',
                $data
            );

            $rma->addData($data);

            /** @var int $rmaId */
            $rmaId = $this->simpleReturnRepository->save($rma);

            $this->eventManager->dispatch(
                'simplereturns_adminhtml_rma_create_save_after',
                ['rma' => $rma]
            );

            if ($notifyCustomer) {
                /* Send New RMA Request email */
                $this->emailTransport->send(
                    'simplereturns/customer/rma_request_new_email_template',
                    'simplereturns/customer/rma_request_new_email_identity',
                    [
                        'orderId' => $order->getRealOrderId(),
                        'uuid' => $rma->getUuid(),
                        'reason' => $this->labelManager->getLabel('reason', $rma->getReason()),
                        'resolution' => $this->labelManager->getLabel('resolution', $rma->getResolution()),
                        'status' => $this->labelManager->getLabel('status', $rma->getStatus()),
                        'comments' => $this->escaper->escapeHtml($rma->getComments()),
                    ],
                    $order->getCustomerEmail(),
                    $order->getCustomerName(),
                    (int) $order->getStoreId()
                );
            }

            /** @var string $viewUrl */
            $viewUrl = $this->urlBuilder->getUrl(
                'simplereturns/rma/view',
                [
                    'rma_id'  => $rmaId,
                    'token'   => $token,
                    '_secure' => true,
                ]
            );
            $resultJson->setData([
                'success' => true,
                'isSimpleReturnsAjax' => true,
                'message' => __('Successfully created RMA.'),
                'viewUrl' => $viewUrl,
            ]);
        } catch (Throwable $e) {
            $resultJson->setData([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }

        return $resultJson;
    }
}
