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
 * @package     AuroraExtensions\SimpleReturns\Controller\Rma\Attachment
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Rma\Attachment;

use AuroraExtensions\ImageProcessor\Api\ImageManagementInterface;
use AuroraExtensions\ModuleComponents\Model\Security\HashContext;
use AuroraExtensions\ModuleComponents\Model\Security\HashContextFactory;
use AuroraExtensions\SimpleReturns\Api\AttachmentRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface;
use AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Model\Adapter\Sales\Order as OrderAdapter;
use AuroraExtensions\SimpleReturns\Model\System\Module\Config as ModuleConfig;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Throwable;

use function __;
use function preg_replace;
use function rtrim;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends Action implements HttpPostActionInterface
{
    private const DATA_GROUP_KEY = 'simplereturns_group_key';
    private const FILE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const REPLACE_CHARS = '-';
    private const SAVE_PATH = '/simplereturns/';
    private const SEARCH_REGEX = '@[^a-zA-Z0-9\.]+@';

    /** @var AttachmentInterfaceFactory $attachmentFactory */
    private $attachmentFactory;

    /** @var AttachmentRepositoryInterface $attachmentRepository */
    private $attachmentRepository;

    /** @var DataPersistorInterface $dataPersistor */
    private $dataPersistor;

    /** @var Filesystem $filesystem */
    private $filesystem;

    /** @var string[] $fileTypes */
    private $fileTypes;

    /** @var FormKeyValidator $formKeyValidator */
    private $formKeyValidator;

    /** @var HashContextFactory $hashContextFactory */
    private $hashContextFactory;

    /** @var ImageManagementInterface $imageManagement */
    private $imageManagement;

    /** @var ModuleConfig $moduleConfig */
    private $moduleConfig;

    /** @var OrderAdapter $orderAdapter */
    private $orderAdapter;

    /** @var RemoteAddress $remoteAddress */
    private $remoteAddress;

    /** @var string|string[] $replaceChars */
    private $replaceChars;

    /** @var ResultJsonFactory $resultJsonFactory */
    private $resultJsonFactory;

    /** @var string $searchRegex */
    private $searchRegex;

    /** @var Json $serializer */
    private $serializer;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param AttachmentInterfaceFactory $attachmentFactory
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param DataPersistorInterface $dataPersistor
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param FormKeyValidator $formKeyValidator
     * @param HashContextFactory $hashContextFactory
     * @param ImageManagementInterface $imageManagement
     * @param ModuleConfig $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param RemoteAddress $remoteAddress
     * @param Json $serializer
     * @param ResultJsonFactory $resultJsonFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param UrlInterface $urlBuilder
     * @param string[] $fileTypes
     * @param string $searchRegex
     * @param string|string[] $replaceChars
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        AttachmentInterfaceFactory $attachmentFactory,
        AttachmentRepositoryInterface $attachmentRepository,
        DataPersistorInterface $dataPersistor,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        FormKeyValidator $formKeyValidator,
        HashContextFactory $hashContextFactory,
        ImageManagementInterface $imageManagement,
        ModuleConfig $moduleConfig,
        OrderAdapter $orderAdapter,
        RemoteAddress $remoteAddress,
        Json $serializer,
        ResultJsonFactory $resultJsonFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder,
        array $fileTypes = self::FILE_TYPES,
        string $searchRegex = self::SEARCH_REGEX,
        $replaceChars = self::REPLACE_CHARS
    ) {
        parent::__construct($context);
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentRepository = $attachmentRepository;
        $this->dataPersistor = $dataPersistor;
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->hashContextFactory = $hashContextFactory;
        $this->imageManagement = $imageManagement;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
        $this->remoteAddress = $remoteAddress;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->urlBuilder = $urlBuilder;
        $this->fileTypes = $fileTypes;
        $this->searchRegex = $searchRegex;
        $this->replaceChars = $replaceChars;
    }

    /**
     * @return ResultJson
     */
    public function execute()
    {
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        /** @var ResultJson $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        if (!$request->isPost()) {
            $resultJson->setData([
                'error' => true,
                'message' => __('Invalid method: Must be POST request.'),
            ]);
            return $resultJson;
        }

        /** @var array $response */
        $response = [];

        /** @var array $attachment */
        $attachments = $request->getFiles('attachments') ?? [];

        /** @var string $mediaPath */
        $mediaPath = $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath();
        $mediaPath = rtrim($mediaPath, '/');

        /** @var string $savePath */
        $savePath = $mediaPath . self::SAVE_PATH;

        /** @var string|null $groupKey */
        $groupKey = $this->dataPersistor->get(self::DATA_GROUP_KEY);

        if (!$groupKey) {
            /* Generate new key for metadata lookup. */
            $groupKey = $this->hashContextFactory->create(['algo' => 'crc32b']);
            $groupKey = (string) $groupKey;

            /**
             * By storing the key in the session, we can allow
             * a user to upload files and store them before an
             * associated RMA record has been created.
             */
            $this->dataPersistor->set(self::DATA_GROUP_KEY, $groupKey);
        }

        /** @var string|null $metadata */
        $metadata = $this->dataPersistor->get($groupKey);
        $metadata = $metadata !== null
            ? $this->serializer->unserialize($metadata) : [];

        /** @var array $attachment */
        foreach ($attachments as $attachment) {
            /** @var string $filename */
            $filename = preg_replace(
                $this->searchRegex,
                $this->replaceChars,
                $attachment['name']
            );

            try {
                /** @var Uploader $uploader */
                $uploader = $this->fileUploaderFactory
                    ->create(['fileId' => $attachment])
                    ->setAllowedExtensions($this->fileTypes)
                    ->setAllowCreateFolders(true)
                    ->setAllowRenameFiles(true)
                    ->setFilesDispersion(true);

                /** @var array $result */
                $result = $uploader->save($savePath, $filename);

                /** @var AttachmentInterface $entity */
                $entity = $this->attachmentFactory->create();

                /** @var string $imageFile */
                $imageFile = rtrim(self::SAVE_PATH, '/') . $result['file'];

                /** @var string $thumbnail */
                $thumbnail = $this->imageManagement->resize($imageFile);

                /** @var HashContext $hashContext */
                $hashContext = $this->hashContextFactory->create(['algo' => 'crc32b']);

                /** @var string $token */
                $token = (string) $hashContext;

                /** @var array $entityData */
                $entityData = [
                    'filename'  => $result['name'],
                    'filepath'  => $result['file'],
                    'filesize'  => $result['size'],
                    'mimetype'  => $result['type'],
                    'thumbnail' => $thumbnail,
                    'token'     => $token,
                ];

                /** @var int $attachmentId */
                $attachmentId = $this->attachmentRepository->save(
                    $entity->addData($entityData)
                );

                $metadata[] = ['attachment_id' => $attachmentId];
                $response[] = [
                    'success' => true,
                    'message' => __(
                        'Successfully uploaded RMA attachment: %1',
                        $result['name']
                    ),
                ];
            } catch (Throwable $e) {
                $response[] = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $this->dataPersistor->set(
            $groupKey,
            $this->serializer->serialize($metadata)
        );

        $resultJson->setData($response);
        return $resultJson;
    }
}
