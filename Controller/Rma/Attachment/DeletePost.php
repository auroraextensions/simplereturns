<?php
/**
 * DeletePost.php
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

namespace AuroraExtensions\SimpleReturns\Controller\Rma\Attachment;

use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
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
    Controller\Result\JsonFactory as ResultJsonFactory,
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

class DeletePost extends Action implements
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

    /** @property ResultJsonFactory $resultJsonFactory */
    protected $resultJsonFactory;

    /** @property Json $serializer */
    protected $serializer;

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
     * @param ResultJsonFactory $resultJsonFactory
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
        ResultJsonFactory $resultJsonFactory,
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
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Execute simplereturns_rma_createPost action.
     *
     * @return Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        /** @var Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        if (!$request->isPost()) {
            return $resultJson;
        }

        /** @var int|string|null $rmaId */
        $rmaId = $request->getParam(self::PARAM_RMA_ID);
        $rmaId = $rmaId !== null && is_numeric($rmaId)
            ? (int) $rmaId
            : null;

        /** @var int|string|null $token */
        $token = $request->getParam(self::PARAM_TOKEN);
        $token = !empty($token) ? $token : null;

        /** @var int|string|null $attachmentKey */
        $attachmentKey = $request->getQuery(self::PARAM_ATTACHMENT_KEY);
        $attachmentKey = !empty($attachmentKey) ? $attachmentKey : null;

        try {
            /** @var SimpleReturnInterface $rma */
            $rma = $this->simpleReturnRepository->getById($rmaId);

            /** @var string|null $data */
            $data = $rma->getAttachments();

            if ($data !== null) {
                /** @var array $entries */
                $entries = $this->serializer->unserialize($data);

                /** @var string $key */
                /** @var string $entry */
                foreach ($entries as $key => $entry) {
                    if ($key === $attachmentKey) {
                        unset($entries[$key]);
                    }
                }

                /* Updated, serialized entries. */
                $data = $this->serializer->serialize($entries);

                $this->simpleReturnRepository->save(
                    $rma->setAttachments($data);
                );
            }
        } catch (NoSuchEntityException $e) {
            /** @todo: Set error details on $resultJson. */
        } catch (LocalizedException $e) {
            /** @todo: Set error details on $resultJson. */
        }

        return $resultJson;
    }
}
