<?php
/**
 * AttachmentManagement.php
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

namespace AuroraExtensions\SimpleReturns\Model\ManagementModel;

use AuroraExtensions\SimpleReturns\{
    Api\AttachmentManagementInterface,
    Api\Data\AttachmentInterface,
    Api\Data\AttachmentInterfaceFactory,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\Filesystem\DirectoryList,
    Filesystem,
    Filesystem\Io\File as FileHandler,
    UrlInterface
};
use Magento\Store\Model\StoreManagerInterface;

class AttachmentManagement implements AttachmentManagementInterface, ModuleComponentInterface
{
    /** @property Filesystem $filesystem */
    protected $filesystem;

    /** @property FileHandler $fileHandler */
    protected $fileHandler;

    /** @property StoreManagerInterface $storeManager */
    protected $storeManager;

    /**
     * @param Filesystem $filesystem
     * @param FileHandler $fileHandler
     * @param StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        Filesystem $filesystem,
        FileHandler $fileHandler,
        StoreManagerInterface $storeManager
    ) {
        $this->filesystem = $filesystem;
        $this->fileHandler = $fileHandler;
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
        $fileData = $this->fileHandler->read($realPath);
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
