<?php
/**
 * SimpleReturn.php
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

use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\SimpleReturn as SimpleReturnResource;
use Magento\Framework\Model\AbstractModel;

class SimpleReturn extends AbstractModel implements SimpleReturnInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(SimpleReturnResource::class);
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
    public function setCreatedAt($createdAt): SimpleReturnInterface
    {
        $this->setData('created_at', $createdAt);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageId(): ?int
    {
        return (int) $this->getData('pkg_id') ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function setPackageId(int $pkgId): SimpleReturnInterface
    {
        $this->setData('pkg_id', $pkgId);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRemoteIp(): string
    {
        return $this->getData('remote_ip');
    }

    /**
     * {@inheritdoc}
     */
    public function setRemoteIp(string $remoteIp): SimpleReturnInterface
    {
        $this->setData('remote_ip', $remoteIp);
        return $this;
    }
}
