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
use AuroraExtensions\SimpleReturns\Api\AttachmentRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Model\Adapter\Sales\Order as OrderAdapter;
use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use AuroraExtensions\SimpleReturns\Model\SystemModel\Module\Config as ModuleConfig;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Throwable;

use function __;
use function is_numeric;
use function trim;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeletePost extends Action implements HttpPostActionInterface
{
    /**
     * @method Redirect getRedirect()
     * @method Redirect getRedirectToPath()
     * @method Redirect getRedirectToUrl()
     */
    use RedirectTrait;

    /** @var AttachmentRepositoryInterface $attachmentRepository */
    private $attachmentRepository;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var Filesystem $filesystem */
    private $filesystem;

    /** @var FormKeyValidator $formKeyValidator */
    private $formKeyValidator;

    /** @var ModuleConfig $moduleConfig */
    private $moduleConfig;

    /** @var OrderAdapter $orderAdapter */
    private $orderAdapter;

    /** @var RemoteAddress $remoteAddress */
    private $remoteAddress;

    /** @var ResultJsonFactory $resultJsonFactory */
    private $resultJsonFactory;

    /** @var Json $serializer */
    private $serializer;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

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
     * @return ResultJson
     */
    public function execute()
    {
        /** @var bool $error */
        $error = false;

        /** @var string $message */
        $message = '';

        /** @var array $response */
        $response = [];

        /** @var RequestInterface $request */
        $request = $this->getRequest();

        /** @var ResultJson $resultJson */
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

        /** @var string $content */
        $content = $request->getContent()
            ?? $this->serializer->serialize([]);

        /** @var array $data */
        $data = (array) $this->serializer->unserialize($content);

        /** @var string|null $fileKey */
        $fileKey = $data['file_key'] ?? null;
        $fileKey = $fileKey !== null && Tokenizer::isHex($fileKey)
            ? trim($fileKey) : null;

        if ($rmaId !== null && $token !== null
            && $fileKey !== null
        ) {
            try {
                /** @var AttachmentInterface $attachment */
                $attachment = $this->attachmentRepository->get($fileKey);
                $this->attachmentRepository->delete($attachment);
            } catch (Throwable $e) {
                $error = true;
                $message = (string) __($e->getMessage());
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
