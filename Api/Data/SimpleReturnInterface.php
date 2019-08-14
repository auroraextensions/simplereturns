<?php
/**
 * SimpleReturnInterface.php
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

use Magento\Sales\Api\Data\OrderInterface;

interface SimpleReturnInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return \AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return \AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return int|null
     */
    public function getPackageId(): ?int;

    /**
     * @param int|null $pkgId
     * @return \AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface
     */
    public function setPackageId(?int $pkgId): SimpleReturnInterface;

    /**
     * @return string
     */
    public function getRemoteIp(): string;

    /**
     * @param string $remoteIp
     * @return \AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface
     */
    public function setRemoteIp(string $remoteIp): SimpleReturnInterface;
}
