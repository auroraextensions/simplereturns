<?php
/**
 * Attachment.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\Data
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\Data;

use AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Attachment as AttachmentResource;
use Magento\Framework\Model\AbstractModel;

class Attachment extends AbstractModel implements AttachmentInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(AttachmentResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt(): string
    {
        return $this->getData('created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt): AttachmentInterface
    {
        $this->setData('created_at', $createdAt);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaId(): ?int
    {
        /** @var int|string|null $rmaId */
        $rmaId = $this->getData('rma_id');
        return $rmaId !== null ? (int) $rmaId : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setRmaId(int $rmaId): AttachmentInterface
    {
        $this->setData('rma_id', $rmaId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename(): ?string
    {
        return $this->getData('filename');
    }

    /**
     * {@inheritdoc}
     */
    public function setFilename(string $filename): AttachmentInterface
    {
        $this->setData('filename', $filename);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesize(): ?int
    {
        /** @var int|string|null $filesize */
        $filesize = $this->getData('filesize');
        return $filesize !== null ? (int) $filesize : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilesize(int $filesize): AttachmentInterface
    {
        $this->setData('filesize', $filesize);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilePath(): ?string
    {
        return $this->getData('filepath');
    }

    /**
     * {@inheritdoc}
     */
    public function setFilePath(string $filePath): AttachmentInterface
    {
        $this->setData('filepath', $filePath);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType(): ?string
    {
        return $this->getData('mimetype');
    }

    /**
     * {@inheritdoc}
     */
    public function setMimeType(string $mimeType): AttachmentInterface
    {
        $this->setData('mimetype', $mimeType);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getThumbnail(): ?string
    {
        return $this->getData('thumbnail');
    }

    /**
     * {@inheritdoc}
     */
    public function setThumbnail(string $filePath): AttachmentInterface
    {
        $this->setData('thumbnail', $filePath);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getToken(): ?string
    {
        return $this->getData('token');
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(string $token): AttachmentInterface
    {
        $this->setData('token', $token);
        return $this;
    }
}
