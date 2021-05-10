<?php
/**
 * CreatePost.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Adminhtml\Rma;

use AuroraExtensions\SimpleReturns\{
    Api\Data\AttachmentInterface,
    Api\Data\SimpleReturnInterface,
    Api\Data\SimpleReturnInterfaceFactory,
    Api\AttachmentRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Component\Http\Request\RedirectTrait,
    Component\System\ModuleConfigTrait,
    Exception\ExceptionFactory,
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Model\Security\Token as Tokenizer,
    Model\Email\Transport\Customer as EmailTransport,
    Model\SystemModel\Module\Config as ModuleConfig,
    Shared\Component\LabelFormatterTrait,
    Shared\ModuleComponentInterface
};
use DateTime;
use DateTimeFactory;
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpPostActionInterface,
    App\Filesystem\DirectoryList,
    Controller\Result\JsonFactory as ResultJsonFactory,
    Data\Form\FormKey\Validator as FormKeyValidator,
    Escaper,
    Event\ManagerInterface as EventManagerInterface,
    Exception\AlreadyExistsException,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    Filesystem,
    HTTP\PhpEnvironment\RemoteAddress,
    Serialize\Serializer\Json,
    Stdlib\DateTime as StdlibDateTime,
    UrlInterface
};
use Magento\MediaStorage\Model\File\UploaderFactory;

use function __;

class CreatePost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    use LabelFormatterTrait, ModuleConfigTrait, RedirectTrait;

    /** @property AttachmentRepositoryInterface $attachmentRepository */
    protected $attachmentRepository;

    /** @property DateTimeFactory $dateTimeFactory */
    protected $dateTimeFactory;

    /** @property EmailTransport $emailTransport */
    protected $emailTransport;

    /** @property Escaper $escaper */
    protected $escaper;

    /** @property EventManagerInterface $eventManager */
    protected $eventManager;

    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property Filesystem $filesystem */
    protected $filesystem;

    /** @property FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @property RemoteAddress $remoteAddress */
    protected $remoteAddress;

    /** @property ResultJsonFactory $resultJsonFactory */
    protected $resultJsonFactory;

    /** @property Json $serializer */
    protected $serializer;

    /** @property SimpleReturnInterfaceFactory $simpleReturnFactory */
    protected $simpleReturnFactory;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @property UrlInterface $urlBuilder */
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
     * @param ModuleConfig $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param RemoteAddress $remoteAddress
     * @param ResultJsonFactory $resultJsonFactory
     * @param Json $serializer
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param UrlInterface $urlBuilder
     * @return void
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
     * Execute simplereturns_rma_createPost action.
     *
     * @return Redirect
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
                $order = $orders[0];

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
                        [
                            'rma' => $rma,
                        ]
                    );

                    /* Send New RMA Request email */
                    $this->emailTransport->send(
                        'simplereturns/customer/rma_request_new_email_template',
                        'simplereturns/customer/rma_request_new_email_identity',
                        [
                            'orderId' => $order->getRealOrderId(),
                            'frontId' => $rma->getFrontId(),
                            'reason' => $this->getFrontLabel('reasons', $rma->getReason()),
                            'resolution' => $this->getFrontLabel('resolutions', $rma->getResolution()),
                            'status' => $this->getFrontLabel('statuses', $rma->getStatus()),
                            'comments' => $this->escaper->escapeHtml($rma->getComments()),
                        ],
                        $order->getCustomerEmail(),
                        $order->getCustomerName(),
                        (int) $order->getStoreId()
                    );

                    /** @var string $viewUrl */
                    $viewUrl = $this->urlBuilder->getUrl('simplereturns/rma/view', [
                        'rma_id'  => $rmaId,
                        'token'   => $token,
                        '_secure' => true,
                    ]);

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
