<?php
/**
 * AttachmentInterface.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Api\Data
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Api\Data;

interface AttachmentInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string|\DateTime $createdAt
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return int|null
     */
    public function getRmaId(): ?int;

    /**
     * @param int $rmaId
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     */
    public function setRmaId(int $rmaId): AttachmentInterface;

    /**
     * @return string|null
     */
    public function getFilename(): ?string;

    /**
     * @param string $filename
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     */
    public function setFilename(string $filename): AttachmentInterface;

    /**
     * @return int|null
     */
    public function getFilesize(): ?int;

    /**
     * @param int $filesize
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     */
    public function setFilesize(int $filesize): AttachmentInterface;

    /**
     * @return string|null
     */
    public function getFilePath(): ?string;

    /**
     * @param string $filePath
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     */
    public function setFilePath(string $filePath): AttachmentInterface;

    /**
     * @return string|null
     */
    public function getMimeType(): ?string;

    /**
     * @param string $mimeType
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     */
    public function setMimeType(string $mimeType): AttachmentInterface;

    /**
     * @return string|null
     */
    public function getThumbnail(): ?string;

    /**
     * @param string $filePath
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     */
    public function setThumbnail(string $filePath): AttachmentInterface;

    /**
     * @return string|null
     */
    public function getToken(): ?string;

    /**
     * @param string $token
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     */
    public function setToken(string $token): AttachmentInterface;
}
