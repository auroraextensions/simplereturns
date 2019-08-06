<?php
/**
 * SearchPost.php
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
    Exception\ExceptionFactory,
    Model\SearchModel\Attachment as AttachmentAdapter,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpPostActionInterface,
    Controller\Result\JsonFactory as ResultJsonFactory,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    Serialize\Serializer\Json,
    UrlInterface,
    View\Result\PageFactory
};
use Magento\Store\Model\StoreManagerInterface;

class SearchPost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    /** @property AttachmentAdapter $attachmentAdapter */
    protected $attachmentAdapter;

    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property ResultJsonFactory $resultJsonFactory */
    protected $resultJsonFactory;

    /** @property Json $serializer */
    protected $serializer;

    /** @property StoreManagerInterface $storeManager */
    protected $storeManager;

    /**
     * @param Context $context
     * @param AttachmentAdapter $attachmentAdapter
     * @param ExceptionFactory $exceptionFactory
     * @param ResultJsonFactory $resultJsonFactory
     * @param Json $serializer
     * @param StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        Context $context,
        AttachmentAdapter $attachmentAdapter,
        ExceptionFactory $exceptionFactory,
        ResultJsonFactory $resultJsonFactory,
        Json $serializer,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->attachmentAdapter = $attachmentAdapter;
        $this->exceptionFactory = $exceptionFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute simplereturns_rma_create action.
     *
     * @return Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var array $results */
        $results = [];

        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        if (!$request->isPost()) {
            return $resultJson;
        }

        /** @var string $content */
        $content = $request->getContent()
            ?? $this->serializer->serialize([]);

        /** @var array $data */
        $data = $this->serializer->unserialize($content);

        /** @var int|string|null $rmaId */
        $rmaId = $data['rma_id'] ?? null;
        $rmaId = $rmaId !== null && is_numeric($rmaId)
            ? (int) $rmaId
            : null;

        if ($rmaId !== null) {
            /** @var string|null $token */
            $token = $data['token'] ?? null;
            $token = !empty($token) ? $token : null;

            if ($token !== null) {
                /** @var string $baseUrl */
                $baseUrl = $this->storeManager
                    ->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                $baseUrl = rtrim($baseUrl, '/');

                /** @var string $mediaUrl */
                $mediaUrl = $baseUrl . self::SAVE_PATH;
                $mediaUrl = rtrim($mediaUrl, '/');

                try {
                    /** @var array $attachments */
                    $attachments = $this->attachmentAdapter
                        ->getRecordsByFields(['rma_id' => $rmaId]);

                    foreach ($attachments as $attachment) {
                        /** @var string $filename */
                        $filename = $attachment->getFilename();

                        /** @var int $filesize */
                        $filesize = $attachment->getFilesize();

                        /** @var string $imagePath */
                        $imagePath = $attachment->getPath()
                            ?? ('/' . $filename);

                        /** @var string $imageUrl */
                        $imageUrl = $mediaUrl . $imagePath;

                        $results[] = [
                            'name' => $filename,
                            'path' => $imageUrl,
                            'size' => $filesize,
                        ];
                    }

                    $resultJson->setData($results);
                } catch (NoSuchEntityException $e) {
                    /* No action required. */
                } catch (LocalizedException $e) {
                    /* No action required. */
                }
            }
        }

        return $resultJson;
    }
}
