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
 * @package     AuroraExtensions\SimpleReturns\Controller\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Rma;

use AuroraExtensions\ModuleComponents\Component\Event\EventManagerTrait;
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
use AuroraExtensions\SimpleReturns\Shared\ModuleComponentInterface;
use AuroraExtensions\SimpleReturns\Model\SystemModel\Module\Config as ModuleConfig;
use DateTime;
use DateTimeFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
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
use function is_numeric;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    use EventManagerTrait,
        ModuleConfigTrait,
        RedirectTrait;

    /** @var AttachmentRepositoryInterface $attachmentRepository */
    protected $attachmentRepository;

    /** @var DataPersistorInterface $dataPersistor */
    protected $dataPersistor;

    /** @var DateTimeFactory $dateTimeFactory */
    protected $dateTimeFactory;

    /** @var EmailTransport $emailTransport */
    protected $emailTransport;

    /** @var Escaper $escaper */
    protected $escaper;

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
     * @param DataPersistorInterface $dataPersistor
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
        DataPersistorInterface $dataPersistor,
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
        Json $serializer,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->attachmentRepository = $attachmentRepository;
        $this->dataPersistor = $dataPersistor;
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
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        if (!$request->isPost() || !$this->formKeyValidator->validate($request)) {
            return $this->getRedirectToPath(static::ROUTE_SALES_GUEST_VIEW);
        }

        /** @var array|null $params */
        $params = $request->getPost('simplereturns');

        if ($params !== null) {
            /** @var int|string|null $orderId */
            $orderId = $request->getParam(static::PARAM_ORDER_ID);
            $orderId = !empty($orderId) ? $orderId : null;

            /** @var string|null $protectCode */
            $protectCode = $request->getParam(static::PARAM_PROTECT_CODE);
            $protectCode = !empty($protectCode) ? $protectCode : null;

            /** @var string|null $reason */
            $reason = $params['reason'] ?? null;
            $reason = $reason !== null && !empty($reason)
                ? $this->escaper->escapeHtml($reason)
                : null;

            /** @var string|null $resolution */
            $resolution = $params['resolution'] ?? null;
            $resolution = $resolution !== null && !empty($resolution)
                ? $this->escaper->escapeHtml($resolution)
                : null;

            /** @var string|null $comments */
            $comments = $params['comments'] ?? null;
            $comments = $comments !== null && !empty($comments)
                ? $this->escaper->escapeHtml($comments)
                : null;

            /** @var array $fields */
            $fields = [
                static::FIELD_INCREMENT_ID => $orderId,
                static::FIELD_PROTECT_CODE => $protectCode,
            ];

            try {
                /** @var OrderInterface[] $orders */
                $orders = $this->orderAdapter
                    ->getOrdersByFields($fields);

                if (!empty($orders)) {
                    /** @var OrderInterface $order */
                    $order = $orders[0];

                    try {
                        /** @var SimpleReturn $rma */
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
                        /** @var SimpleReturn $rma */
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
                            'status'     => ModuleConfig::DEFAULT_RMA_STATUS_CODE,
                            'reason'     => $reason,
                            'resolution' => $resolution,
                            'comments'   => $comments,
                            'remote_ip'  => $remoteIp,
                            'token'      => $token,
                            'created_at' => $createdTime,
                        ];

                        $this->dispatchEvent('simplereturns_rma_create_save_before', $data);

                        /** @var int $rmaId */
                        $rmaId = $this->simpleReturnRepository->save(
                            $rma->addData($data)
                        );

                        $this->dispatchEvent('simplereturns_rma_create_save_after', [
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
                                $attachmentId = $attachmentId !== null && is_numeric($attachmentId)
                                    ? (int) $attachmentId
                                    : null;

                                if ($attachmentId !== null) {
                                    /** @var AttachmentInterface $attachment */
                                    $attachment = $this->attachmentRepository->getById($attachmentId);

                                    $this->attachmentRepository->save(
                                        $attachment->setRmaId($rmaId)
                                    );
                                }
                            }

                            /* Clear attachment metadata, group key from session. */
                            $this->dataPersistor->clear($groupKey);
                            $this->dataPersistor->clear(static::DATA_GROUP_KEY);
                        }

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
                    } catch (AlreadyExistsException $e) {
                        throw $e;
                    } catch (LocalizedException $e) {
                        throw $e;
                    }
                }

                /** @var LocalizedException $exception */
                $exception = $this->exceptionFactory->create(
                    LocalizedException::class,
                    __('Unable to create RMA request.')
                );

                throw $exception;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->getRedirectToPath(static::ROUTE_SALES_GUEST_VIEW);
    }
}
