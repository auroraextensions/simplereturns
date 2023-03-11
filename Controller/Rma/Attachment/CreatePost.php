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
use AuroraExtensions\SimpleReturns\{
    Api\Data\AttachmentInterface,
    Api\Data\AttachmentInterfaceFactory,
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
    App\Request\DataPersistorInterface,
    Controller\Result\JsonFactory as ResultJsonFactory,
    Controller\Result\Redirect as ResultRedirect,
    Data\Form\FormKey\Validator as FormKeyValidator,
    Filesystem,
    HTTP\PhpEnvironment\RemoteAddress,
    Serialize\Serializer\Json,
    UrlInterface
};
use Magento\MediaStorage\Model\File\UploaderFactory;
use Throwable;

use function __;
use function rtrim;
use function str_replace;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    /** @var AttachmentInterfaceFactory $attachmentFactory */
    protected $attachmentFactory;

    /** @var AttachmentRepositoryInterface $attachmentRepository */
    protected $attachmentRepository;

    /** @var DataPersistorInterface $dataPersistor */
    protected $dataPersistor;

    /** @var Filesystem $filesystem */
    protected $filesystem;

    /** @var FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

    /** @var ImageManagementInterface $imageManagement */
    protected $imageManagement;

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
     * @param AttachmentInterfaceFactory $attachmentFactory
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param DataPersistorInterface $dataPersistor
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ImageManagementInterface $imageManagement
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
        AttachmentInterfaceFactory $attachmentFactory,
        AttachmentRepositoryInterface $attachmentRepository,
        DataPersistorInterface $dataPersistor,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        FormKeyValidator $formKeyValidator,
        ImageManagementInterface $imageManagement,
        ModuleConfig $moduleConfig,
        OrderAdapter $orderAdapter,
        RemoteAddress $remoteAddress,
        Json $serializer,
        ResultJsonFactory $resultJsonFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentRepository = $attachmentRepository;
        $this->dataPersistor = $dataPersistor;
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->imageManagement = $imageManagement;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
        $this->remoteAddress = $remoteAddress;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Execute simplereturns_rma_attachment_createPost action.
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
            $groupKey = Tokenizer::createToken();

            /**
             * By storing the key in the session, we can allow
             * a user to upload files and store them before an
             * associated RMA record has been created.
             */
            $this->dataPersistor->set(self::DATA_GROUP_KEY, $groupKey);
        }

        /** @var string|array|null $metadata */
        $metadata = $this->dataPersistor->get($groupKey);
        $metadata = $metadata !== null
            ? $this->serializer->unserialize($metadata) : [];

        /** @var array $attachment */
        foreach ($attachments as $attachment) {
            /** @var string $filename */
            $filename = str_replace(
                ' ',
                '_',
                $attachment['name']
            );

            try {
                /** @var Magento\MediaStorage\Model\File\Uploader $uploader */
                $uploader = $this->fileUploaderFactory
                    ->create(['fileId' => $attachment])
                    ->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']) /** @todo: Pull file extensions dynamically. */
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

                /** @var array $entityData */
                $entityData = [
                    'filename'  => $result['name'],
                    'filepath'  => $result['file'],
                    'filesize'  => $result['size'],
                    'mimetype'  => $result['type'],
                    'thumbnail' => $thumbnail,
                    'token'     => Tokenizer::createToken(),
                ];

                /** @var int $attachmentId */
                $attachmentId = $this->attachmentRepository->save(
                    $entity->addData($entityData)
                );

                $metadata[] = [
                    'attachment_id' => $attachmentId,
                ];

                $response[] = [
                    'success' => true,
                    'message' => __('Successfully uploaded RMA attachment: %1', $result['name']),
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
