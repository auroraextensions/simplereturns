<?php
/**
 * AttachmentManagement.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\Management
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\Management;

use AuroraExtensions\SimpleReturns\Api\AttachmentManagementInterface;
use AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface;
use AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

use function base64_encode;
use function rtrim;

class AttachmentManagement implements AttachmentManagementInterface
{
    private const SAVE_PATH = '/simplereturns/';

    /** @var Filesystem $filesystem */
    private $filesystem;

    /** @var File $file */
    private $file;

    /** @var StoreManagerInterface $storeManager */
    private $storeManager;

    /**
     * @param File $file
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        File $file,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->file = $file;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
    }

    /**
     * Get file as data URI.
     *
     * @param AttachmentInterface $attachment
     * @return string
     */
    public function getFileDataUri(AttachmentInterface $attachment): string
    {
        /** @var string $filePath */
        $filePath = $attachment->getFilePath()
            ?? ('/' . $attachment->getFilename());

        /** @var string $realPath */
        $realPath = $this->getSavePath() . $filePath;

        /** @var string|bool $fileData */
        $fileData = $this->file->read($realPath);
        $fileData = $fileData !== false ? base64_encode($fileData) : '';
        return 'data:' . $attachment->getMimeType() . ';base64,' . $fileData;
    }

    /**
     * Get attachment file URL.
     *
     * @param AttachmentInterface $attachment
     * @return string
     */
    public function getFileUrl(AttachmentInterface $attachment): string
    {
        /** @var string $baseUrl */
        $baseUrl = $this->storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $baseUrl = rtrim($baseUrl, '/');

        /** @var string $mediaUrl */
        $mediaUrl = $baseUrl . self::SAVE_PATH;
        $mediaUrl = rtrim($mediaUrl, '/');
        return ($mediaUrl . $attachment->getFilePath());
    }

    /**
     * @return string
     */
    public function getMediaPath(): string
    {
        /** @var string $mediaPath */
        $mediaPath = $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath();
        $mediaPath = rtrim($mediaPath, '/');
        return $mediaPath;
    }

    /**
     * @return string
     */
    public function getSavePath(): string
    {
        /** @var string $mediaPath */
        $mediaPath = $this->getMediaPath();

        /** @var string $savePath */
        $savePath = $mediaPath . self::SAVE_PATH;
        $savePath = rtrim($savePath, '/');
        return $savePath;
    }
}
