<?php
/**
 * DeletePost.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Controller\Rma\Attachment
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Rma\Attachment;

use AuroraExtensions\ModuleComponents\Component\Http\Request\RedirectTrait;
use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Api\Data\AttachmentInterface,
    Api\Data\SimpleReturnInterface,
    Api\AttachmentRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Model\Security\Token as Tokenizer,
    Model\SystemModel\Module\Config as ModuleConfig,
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

use function __;
use function is_numeric;
use function trim;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeletePost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    use RedirectTrait;

    /** @var AttachmentRepositoryInterface $attachmentRepository */
    protected $attachmentRepository;

    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var Filesystem $filesystem */
    protected $filesystem;

    /** @var FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

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

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @var UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param AttachmentRepositoryInterface $attachmentRepository
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
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        AttachmentRepositoryInterface $attachmentRepository,
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
        $this->attachmentRepository = $attachmentRepository;
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
     * Execute simplereturns_rma_attachment_deletePost action.
     *
     * @return Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var bool $error */
        $error = false;

        /** @var string $message */
        $message = '';

        /** @var array $response */
        $response = [];

        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        /** @var Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        if (!$request->isPost()) {
            $response += [
                'error' => true,
                'message' => (string) __('Invalid method: Must be POST request.'),
            ];
            $resultJson->setData($response);
            return $resultJson;
        }

        /** @var string $content */
        $content = $request->getContent()
            ?? $this->serializer->serialize([]);

        /** @var array $data */
        $data = $this->serializer->unserialize($content);

        /** @var int|string|null $rmaId */
        $rmaId = $data['rma_id'] ?? null;
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        /** @var string|null $token */
        $token = $data['token'] ?? null;
        $token = !empty($token) ? $token : null;

        if ($rmaId !== null && $token !== null) {
            /** @var string|null $fileKey */
            $fileKey = $data['file_key'] ?? null;
            $fileKey = $fileKey !== null && Tokenizer::isHex($fileKey)
                ? trim($fileKey) : null;

            if ($fileKey !== null) {
                try {
                    /** @var AttachmentInterface $attachment */
                    $attachment = $this->attachmentRepository->get($fileKey);
                    $this->attachmentRepository->delete($attachment);
                } catch (NoSuchEntityException | LocalizedException $e) {
                    $error = true;
                    $message = (string) __($e->getMessage());
                }
            }
        }

        $response += [
            'error' => $error,
            'message' => $message,
        ];
        $resultJson->setData($response);
        return $resultJson;
    }
}
