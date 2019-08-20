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
    Api\Data\AttachmentInterface,
    Api\Data\SimpleReturnInterface,
    Api\Data\SimpleReturnInterfaceFactory,
    Api\AttachmentRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Model\AdapterModel\Security\Token as Tokenizer,
    Model\SystemModel\Module\Config as ModuleConfig,
    Shared\Action\Redirector,
    Shared\ModuleComponentInterface
};
use DateTime;
use DateTimeFactory;
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpPostActionInterface,
    App\Filesystem\DirectoryList,
    App\Request\DataPersistorInterface,
    Controller\Result\Redirect as ResultRedirect,
    Data\Form\FormKey\Validator as FormKeyValidator,
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

class CreatePost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    /** @see AuroraExtensions\SimpleReturns\Shared\Action\Redirector */
    use Redirector {
        Redirector::__initialize as protected;
    }

    /** @property AttachmentRepositoryInterface $attachmentRepository */
    protected $attachmentRepository;

    /** @property DataPersistorInterface $dataPersistor */
    protected $dataPersistor;

    /** @property DateTimeFactory $dateTimeFactory */
    protected $dateTimeFactory;

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
     * @param DataPersistorInterface $dataPersistor
     * @param DateTimeFactory $dateTimeFactory
     * @param ExceptionFactory $exceptionFactory
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ModuleConfig $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param RemoteAddress $remoteAddress
     * @param Json $serializer
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param UrlInterface $urlBuilder
     * @return void
     */
    public function __construct(
        Context $context,
        AttachmentRepositoryInterface $attachmentRepository,
        DataPersistorInterface $dataPersistor,
        DateTimeFactory $dateTimeFactory,
        ExceptionFactory $exceptionFactory,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        FormKeyValidator $formKeyValidator,
        ModuleConfig $moduleConfig,
        OrderAdapter $orderAdapter,
        RemoteAddress $remoteAddress,
        Json $serializer,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->__initialize();
        $this->attachmentRepository = $attachmentRepository;
        $this->dataPersistor = $dataPersistor;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
        $this->remoteAddress = $remoteAddress;
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

        if (!$request->isPost() || !$this->formKeyValidator->validate($request)) {
            return $this->getRedirectToPath(self::ROUTE_SALES_GUEST_VIEW);
        }

        /** @var array|null $params */
        $params = $request->getPost('simplereturns');

        if ($params !== null) {
            /** @var int|string|null $orderId */
            $orderId = $request->getParam(self::PARAM_ORDER_ID);
            $orderId = !empty($orderId) ? $orderId : null;

            /** @var string|null $protectCode */
            $protectCode = $request->getParam(self::PARAM_PROTECT_CODE);
            $protectCode = !empty($protectCode) ? $protectCode : null;

            /** @var string|null $reason */
            $reason = $params['reason'] ?? null;
            $reason = !empty($reason) ? $reason : null;

            /** @var string|null $resolution */
            $resolution = $params['resolution'] ?? null;
            $resolution = !empty($resolution) ? $resolution : null;

            /** @var string|null $comments */
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

                        /** @var int $rmaId */
                        $rmaId = $this->simpleReturnRepository->save(
                            $rma->addData($data)
                        );

                        /** @var string|null $groupKey */
                        $groupKey = $this->dataPersistor->get(self::DATA_GROUP_KEY);

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

                            /* Clear attachment metadata from session. */
                            $this->dataPersistor->clear($groupKey);

                            /* Clear group key from session. */
                            $this->dataPersistor->clear(self::DATA_GROUP_KEY);
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

                        return $this->getRedirectToUrl($viewUrl);
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
                $this->messageManager->addError($e->getMessage());
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        return $this->getRedirectToPath(self::ROUTE_SALES_GUEST_VIEW);
    }
}
