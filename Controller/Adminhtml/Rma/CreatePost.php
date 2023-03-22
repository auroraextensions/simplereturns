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

use AuroraExtensions\ModuleComponents\Component\Http\Request\RedirectTrait;
use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Api\AttachmentRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Component\System\ModuleConfigTrait;
use AuroraExtensions\SimpleReturns\Model\AdapterModel\Sales\Order as OrderAdapter;
use AuroraExtensions\SimpleReturns\Model\Display\LabelManager;
use AuroraExtensions\SimpleReturns\Model\Email\Transport\Customer as EmailTransport;
use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use AuroraExtensions\SimpleReturns\Model\SystemModel\Module\Config as ModuleConfig;
use AuroraExtensions\SimpleReturns\Shared\ModuleComponentInterface;
use DateTime;
use DateTimeFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

use function __;
use function array_shift;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    use ModuleConfigTrait, RedirectTrait;

    /** @var AttachmentRepositoryInterface $attachmentRepository */
    protected $attachmentRepository;

    /** @var DateTimeFactory $dateTimeFactory */
    protected $dateTimeFactory;

    /** @var EmailTransport $emailTransport */
    protected $emailTransport;

    /** @var Escaper $escaper */
    protected $escaper;

    /** @var EventManagerInterface $eventManager */
    protected $eventManager;

    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var Filesystem $filesystem */
    protected $filesystem;

    /** @var FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

    /** @var LabelManager $labelManager */
    protected $labelManager;

    /** @var ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @var OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @var RemoteAddress $remoteAddress */
    protected $remoteAddress;

    /** @var ResultJsonFactory $resultJsonFactory */
    protected $resultJsonFactory;

    /** @var Json $serializer */
    protected $serializer;

    /** @var SimpleReturnInterfaceFactory $simpleReturnFactory */
    protected $simpleReturnFactory;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @var UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param DateTimeFactory $dateTimeFactory
     * @param EmailTransport $emailTransport
     * @param Escaper $escaper
     * @param EventManagerInterface $eventManager
     * @param ExceptionFactory $exceptionFactory
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param FormKeyValidator $formKeyValidator
     * @param LabelManager $labelManager
     * @param ModuleConfig $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param RemoteAddress $remoteAddress
     * @param ResultJsonFactory $resultJsonFactory
     * @param Json $serializer
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param UrlInterface $urlBuilder
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        AttachmentRepositoryInterface $attachmentRepository,
        DateTimeFactory $dateTimeFactory,
        EmailTransport $emailTransport,
        Escaper $escaper,
        EventManagerInterface $eventManager,
        ExceptionFactory $exceptionFactory,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        FormKeyValidator $formKeyValidator,
        LabelManager $labelManager,
        ModuleConfig $moduleConfig,
        OrderAdapter $orderAdapter,
        RemoteAddress $remoteAddress,
        ResultJsonFactory $resultJsonFactory,
        Json $serializer,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->attachmentRepository = $attachmentRepository;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->emailTransport = $emailTransport;
        $this->escaper = $escaper;
        $this->eventManager = $eventManager;
        $this->exceptionFactory = $exceptionFactory;
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->labelManager = $labelManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
        $this->remoteAddress = $remoteAddress;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer;
        $this->simpleReturnFactory = $simpleReturnFactory;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        /** @var array $response */
        $response = [];

        /** @var Magento\Framework\Controller\Result\Json $resultJson */
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
        $status = !empty($status) ? $this->escaper->escapeHtml($status) : null;

        /** @var string|null $reason */
        $reason = $request->getPostValue('reason');
        $reason = !empty($reason) ? $this->escaper->escapeHtml($reason) : null;

        /** @var string|null $resolution */
        $resolution = $request->getPostValue('resolution');
        $resolution = !empty($resolution) ? $this->escaper->escapeHtml($resolution) : null;

        /** @var string|null $comments */
        $comments = $request->getPostValue('comments');
        $comments = !empty($comments) ? $this->escaper->escapeHtml($comments) : null;

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
                $order = array_shift($orders);

                try {
                    /** @var SimpleReturnInterface $rma */
                    $rma = $this->simpleReturnRepository->get($order);

                    /** @note Consider possible redirect to RMA view page. */
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

                    /** @var string $token */
                    $token = Tokenizer::createToken();

                    /** @var DateTime $createdTime */
                    $createdTime = $this->dateTimeFactory->create();

                    /** @var array $data */
                    $data = [
                        'order_id'   => $orderId,
                        'status'     => $status,
                        'reason'     => $reason,
                        'resolution' => $resolution,
                        'comments'   => $comments,
                        'remote_ip'  => $remoteIp,
                        'token'      => $token,
                        'created_at' => $createdTime,
                    ];

                    $this->eventManager->dispatch(
                        'simplereturns_adminhtml_rma_create_save_before',
                        $data
                    );

                    /** @var int $rmaId */
                    $rmaId = $this->simpleReturnRepository->save($rma->addData($data));
                    $this->eventManager->dispatch(
                        'simplereturns_adminhtml_rma_create_save_after',
                        ['rma' => $rma]
                    );

                    /* Send New RMA Request email */
                    $this->emailTransport->send(
                        'simplereturns/customer/rma_request_new_email_template',
                        'simplereturns/customer/rma_request_new_email_identity',
                        [
                            'orderId' => $order->getRealOrderId(),
                            'frontId' => $rma->getFrontId(),
                            'reason' => $this->labelManager->getLabel('reason', $rma->getReason()),
                            'resolution' => $this->labelManager->getLabel('resolution', $rma->getResolution()),
                            'status' => $this->labelManager->getLabel('status', $rma->getStatus()),
                            'comments' => $this->escaper->escapeHtml($rma->getComments()),
                        ],
                        $order->getCustomerEmail(),
                        $order->getCustomerName(),
                        (int) $order->getStoreId()
                    );

                    /** @var string $viewUrl */
                    $viewUrl = $this->urlBuilder->getUrl(
                        'simplereturns/rma/view',
                        [
                            'rma_id'  => $rmaId,
                            'token'   => $token,
                            '_secure' => true,
                        ]
                    );
                    return $resultJson->setData([
                        'success' => true,
                        'isSimpleReturnsAjax' => true,
                        'message' => __('Successfully created RMA.'),
                        'viewUrl' => $viewUrl,
                    ]);
                } catch (AlreadyExistsException | LocalizedException $e) {
                    throw $e;
                }
            }

            /** @var LocalizedException $exception */
            $exception = $this->exceptionFactory->create(
                LocalizedException::class,
                __('Unable to create RMA request.')
            );
            throw $exception;
        } catch (NoSuchEntityException | LocalizedException $e) {
            $response = [
                'error' => true,
                'messages' => [$e->getMessage()],
            ];
        }

        return $resultJson->setData($response);
    }
}
