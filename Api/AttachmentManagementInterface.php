<?php
/**
 * AttachmentManagementInterface.php
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

namespace AuroraExtensions\SimpleReturns\Api;

interface AttachmentManagementInterface
{
    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface $attachment
     * @return string
     */
    public function getFileDataUri(Data\AttachmentInterface $attachment): string;

    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface $attachment
     * @return string
     */
    public function getFileUrl(Data\AttachmentInterface $attachment): string;
}
