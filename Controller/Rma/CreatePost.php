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
    Model\AdapterModel\Security\Token as Tokenizer,
    Model\SystemModel\Module\Config as ModuleConfig,
    Shared\Action\Redirector,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpPostActionInterface,
    App\Filesystem\DirectoryList,
    Controller\Result\Redirect as ResultRedirect,
    Data\Form\FormKey\Validator as FormKeyValidator,
    Exception\AlreadyExistsException,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    Filesystem,
    HTTP\PhpEnvironment\RemoteAddress,
    Serialize\Serializer\Json,
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

            /** @var array $attachments */
            $attachments = $request->getFiles('attachments') ?? [];
            $attachments = !empty($attachments) ? $attachments : null;

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

                        /** @var string $status */
                        $status = $this->moduleConfig->getDefaultStatus();

                        /** @var array $data */
                        $data = [
                            'order_id'   => $orderId,
                            'status'     => $status,
                            'reason'     => $reason,
                            'resolution' => $resolution,
                            'comments'   => $comments,
                            'remote_ip'  => $remoteIp,
                            'token'      => $token,
                        ];

                        /* Include file metadata, if needed. */
                        if ($attachments !== null) {
                            /** @var array $metadata */
                            $metadata = [];

                            /** @var string $mediaPath */
                            $mediaPath = $this->filesystem
                                ->getDirectoryRead(DirectoryList::MEDIA)
                                ->getAbsolutePath();
                            $mediaPath = rtrim($mediaPath, '/');

                            /** @var array $attachment */
                            foreach ($attachments as $attachment) {
                                /** @var string $savePath */
                                $savePath = $mediaPath . self::SAVE_PATH;

                                /** @var Magento\MediaStorage\Model\File\Uploader $uploader */
                                $uploader = $this->fileUploaderFactory
                                    ->create(['fileId' => $attachment])
                                    ->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']) /** @todo: Pull file extensions dynamically. */
                                    ->setAllowCreateFolders(true)
                                    ->setAllowRenameFiles(true)
                                    ->setFilesDispersion(true);

                                /** @var string $filename */
                                $filename = str_replace(
                                    ' ',
                                    '_',
                                    $attachment['name']
                                );

                                /** @var array $result */
                                $result = $uploader->save($savePath, $filename);

                                /** @var string $fileKey */
                                $fileKey = Tokenizer::createToken();

                                /* Include file metadata with RMA entry. */
                                $metadata[$fileKey] = $result['file'];
                            }

                            $data['attachments'] = $this->serializer->serialize($metadata);
                        }

                        /** @var int $rmaId */
                        $rmaId = $this->simpleReturnRepository->save(
                            $rma->addData($data)
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
