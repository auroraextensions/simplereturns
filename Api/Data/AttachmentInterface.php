<?php
/**
 * AttachmentInterface.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package        AuroraExtensions_SimpleReturns
 * @copyright      Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license        Aurora Extensions EULA
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
     * @param string $createdAt
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
     * @return string|null
     */
    public function getMimeType(): ?string;

    /**
     * @param string $mimeType
     * @return \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface
     */
    public function setMimeType(string $mimeType): AttachmentInterface;
}
