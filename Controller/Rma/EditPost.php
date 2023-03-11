<?php
/**
 * EditPost.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Controller\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Rma;

use AuroraExtensions\ModuleComponents\Component\Event\EventManagerTrait;
use AuroraExtensions\ModuleComponents\Component\Http\Request\RedirectTrait;
use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Api\Data\AttachmentInterface,
    Api\Data\SimpleReturnInterface,
    Api\Data\SimpleReturnInterfaceFactory,
    Api\AttachmentRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Component\System\ModuleConfigTrait,
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Model\Email\Transport\Customer as EmailTransport,
    Model\Security\Token as Tokenizer,
    Shared\Component\LabelFormatterTrait,
    Shared\ModuleComponentInterface,
    Csi\System\Module\ConfigInterface
};
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpPostActionInterface,
    App\Request\DataPersistorInterface,
    Controller\Result\Redirect as ResultRedirect,
    Data\Form\FormKey\Validator as FormKeyValidator,
    Escaper,
    Event\ManagerInterface as EventManagerInterface,
    Exception\AlreadyExistsException,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    HTTP\PhpEnvironment\RemoteAddress,
    Serialize\Serializer\Json,
    UrlInterface
};

use function array_shift;
use function is_numeric;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    use EventManagerTrait,
        ModuleConfigTrait,
        LabelFormatterTrait,
        RedirectTrait;

    /** @var AttachmentRepositoryInterface $attachmentRepository */
    protected $attachmentRepository;

    /** @var DataPersistorInterface $dataPersistor */
    protected $dataPersistor;

    /** @var EmailTransport $emailTransport */
    protected $emailTransport;

    /** @var Escaper $escaper */
    protected $escaper;

    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

    /** @var OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @var RemoteAddress $remoteAddress */
    protected $remoteAddress;

    /** @var Json $serializer */
    protected $serializer;

    /** @var SimpleReturnInterfaceFactory $simpleReturnFactory */
    protected $simpleReturnFactory;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @var Tokenizer $tokenizer */
    protected $tokenizer;

    /** @var UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param DataPersistorInterface $dataPersistor
     * @param EmailTransport $emailTransport
     * @param Escaper $escaper
     * @param EventManagerInterface $eventManager
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ConfigInterface $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param RemoteAddress $remoteAddress
     * @param Json $serializer
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param Tokenizer $tokenizer
     * @param UrlInterface $urlBuilder
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        AttachmentRepositoryInterface $attachmentRepository,
        DataPersistorInterface $dataPersistor,
        EmailTransport $emailTransport,
        Escaper $escaper,
        EventManagerInterface $eventManager,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        ConfigInterface $moduleConfig,
        OrderAdapter $orderAdapter,
        RemoteAddress $remoteAddress,
        Json $serializer,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        Tokenizer $tokenizer,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->attachmentRepository = $attachmentRepository;
        $this->dataPersistor = $dataPersistor;
        $this->emailTransport = $emailTransport;
        $this->escaper = $escaper;
        $this->eventManager = $eventManager;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
        $this->remoteAddress = $remoteAddress;
        $this->serializer = $serializer;
        $this->simpleReturnFactory = $simpleReturnFactory;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->tokenizer = $tokenizer;
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

        if (!$request->isPost() || !$this->formKeyValidator->validate($request)) {
            return $this->getRedirectToPath(static::ROUTE_SALES_GUEST_VIEW);
        }

        /** @var int|string|null $rmaId */
        $rmaId = $request->getParam(static::PARAM_RMA_ID);
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        if ($rmaId !== null) {
            /** @var array|null $params */
            $params = $request->getPost('simplereturns');

            /** @var string|null $reason */
            $reason = $params['reason'] ?? null;
            $reason = !empty($reason) ? $reason : null;

            /** @var string|null $resolution */
            $resolution = $params['resolution'] ?? null;
            $resolution = !empty($resolution) ? $resolution : null;

            /** @var string|null $comments */
            $comments = $params['comments'] ?? null;
            $comments = !empty($comments) ? $comments : null;

            /** @var string|null $token */
            $token = $request->getParam(static::PARAM_TOKEN);
            $token = !empty($token) ? $token : null;

            /** @var string $remoteIp */
            $remoteIp = $this->remoteAddress->getRemoteAddress();

            try {
                /** @var SimpleReturnInterface $rma */
                $rma = $this->simpleReturnRepository->getById($rmaId);

                if (!Tokenizer::isEqual($token, $rma->getToken())) {
                    /** @var LocalizedException $exception */
                    $exception = $this->exceptionFactory->create(
                        LocalizedException::class
                    );

                    throw $exception;
                }

                /** @var int|string $orderId */
                $orderId = $rma->getOrderId();

                /** @var string $orderId */
                $status = $rma->getStatus();

                /** @var array $data */
                $data = [
                    'rma_id' => $rmaId,
                    'status' => $status,
                    'reason' => $reason,
                    'resolution' => $resolution,
                    'comments' => $comments,
                    'remote_ip' => $remoteIp,
                    'token' => $token,
                ];

                $this->dispatchEvent('simplereturns_rma_edit_save_before', $data);

                $this->simpleReturnRepository->save(
                    $rma->setData($data)
                );

                $this->dispatchEvent('simplereturns_rma_edit_save_after', [
                    'rma' => $rma,
                ]);

                /** @var string|null $groupKey */
                $groupKey = $this->dataPersistor->get(static::DATA_GROUP_KEY);

                /* Update attachments with new RMA ID. */
                if ($groupKey !== null) {
                    /** @var array $metadata */
                    $metadata = $this->serializer->unserialize(
                        $this->dataPersistor->get($groupKey)
                            ?? $this->serializer->serialize([])
                    );

                    /** @var array $metadatum */
                    foreach ($metadata as $metadatum) {
                        /** @var int|string|null $attachmentId */
                        $attachmentId = $metadatum['attachment_id'] ?? null;
                        $attachmentId = is_numeric($attachmentId) ? (int) $attachmentId : null;

                        if ($attachmentId !== null) {
                            /** @var AttachmentInterface $attachment */
                            $attachment = $this->attachmentRepository
                                ->getById($attachmentId);

                            $this->attachmentRepository->save(
                                $attachment->setRmaId($rmaId)
                            );
                        }
                    }

                    /* Clear attachment metadata, group key from session. */
                    $this->dataPersistor->clear($groupKey);
                    $this->dataPersistor->clear(static::DATA_GROUP_KEY);
                }

                /** @var OrderInterface[] $orders */
                $orders = $this->orderAdapter
                    ->getOrdersByFields(['entity_id' => $orderId]);

                /** @var OrderInterface $order */
                $order = array_shift($orders);

                /* Send Updated RMA Request email */
                $this->emailTransport->send(
                    'simplereturns/customer/rma_request_update_email_template',
                    'simplereturns/customer/rma_request_update_email_identity',
                    [
                        'orderId' => $order->getRealOrderId(),
                        'frontId' => $rma->getFrontId(),
                        'reason' => $this->getFrontLabel('reasons', $reason),
                        'resolution' => $this->getFrontLabel('resolutions', $resolution),
                        'status' => $this->getFrontLabel('statuses', $status),
                        'comments' => $this->escaper->escapeHtml($comments),
                    ],
                    $order->getCustomerEmail(),
                    $order->getCustomerName(),
                    (int) $order->getStoreId()
                );

                /** @var string $redirectUrl */
                $redirectUrl = $this->urlBuilder->getUrl(
                    'simplereturns/rma/view',
                    [
                        'rma_id'  => $rmaId,
                        'token'   => $token,
                        '_secure' => true,
                    ]
                );

                return $this->getRedirectToUrl($redirectUrl);
            } catch (NoSuchEntityException | LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->getRedirectToPath(static::ROUTE_SALES_GUEST_VIEW);
    }
}
