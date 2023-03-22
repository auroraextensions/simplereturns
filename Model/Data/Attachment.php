<?php
/**
 * Attachment.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\DataModel;

use AuroraExtensions\SimpleReturns\{
    Api\Data\AttachmentInterface,
    Model\ResourceModel\Attachment as AttachmentResourceModel,
    Shared\ModuleComponentInterface
};
use Magento\Framework\Model\AbstractModel;

class Attachment extends AbstractModel implements
    AttachmentInterface,
    ModuleComponentInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(AttachmentResourceModel::class);
    }

    /**
     * @return string
     */
    public function getFrontId(): string
    {
        return sprintf(
            self::FORMAT_FRONT_ID,
            $this->getId()
        );
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getData('created_at');
    }

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt): AttachmentInterface
    {
        $this->setData('created_at', $createdAt);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRmaId(): ?int
    {
        /** @var int|string|null $rmaId */
        $rmaId = $this->getData('rma_id');

        return $rmaId !== null ? (int) $rmaId : null;
    }

    /**
     * @param int $rmaId
     * @return AttachmentInterface
     */
    public function setRmaId(int $rmaId): AttachmentInterface
    {
        $this->setData('rma_id', $rmaId);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->getData('filename');
    }

    /**
     * @param string $filename
     * @return AttachmentInterface
     */
    public function setFilename(string $filename): AttachmentInterface
    {
        $this->setData('filename', $filename);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getFilesize(): ?int
    {
        /** @var int|string|null $filesize */
        $filesize = $this->getData('filesize');

        return $filesize !== null ? (int) $filesize : null;
    }

    /**
     * @param int $filesize
     * @return AttachmentInterface
     */
    public function setFilesize(int $filesize): AttachmentInterface
    {
        $this->setData('filesize', $filesize);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->getData('filepath');
    }

    /**
     * @param string $filePath
     * @return AttachmentInterface
     */
    public function setFilePath(string $filePath): AttachmentInterface
    {
        $this->setData('filepath', $filePath);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->getData('mimetype');
    }

    /**
     * @param string $mimeType
     * @return AttachmentInterface
     */
    public function setMimeType(string $mimeType): AttachmentInterface
    {
        $this->setData('mimetype', $mimeType);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getThumbnail(): ?string
    {
        return $this->getData('thumbnail');
    }

    /**
     * @param string $filePath
     * @return AttachmentInterface
     */
    public function setThumbnail(string $filePath): AttachmentInterface
    {
        $this->setData('thumbnail', $filePath);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->getData('token');
    }

    /**
     * @param string $token
     * @return AttachmentInterface
     */
    public function setToken(string $token): AttachmentInterface
    {
        $this->setData('token', $token);

        return $this;
    }
}
