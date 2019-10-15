<?php
/**
 * SimpleReturn.php
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
    Api\Data\SimpleReturnInterface,
    Model\ResourceModel\SimpleReturn as SimpleReturnResourceModel,
    Shared\Component\FormatterTrait,
    Shared\ModuleComponentInterface
};
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderInterface;

class SimpleReturn extends AbstractModel implements
    SimpleReturnInterface,
    ModuleComponentInterface
{
    use FormatterTrait;

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(SimpleReturnResourceModel::class);
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
    public function setCreatedAt($createdAt): SimpleReturnInterface
    {
        $this->setData('created_at', $createdAt);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPackageId(): ?int
    {
        /** @var int|string|null $pkgId */
        $pkgId = $this->getData('pkg_id') ?? null;
        $pkgId = $pkgId !== null && is_numeric($pkgId)
            ? (int) $pkgId
            : null;

        return $pkgId;
    }

    /**
     * @param int|null $pkgId
     * @return $this
     */
    public function setPackageId(?int $pkgId): SimpleReturnInterface
    {
        $this->setData('pkg_id', $pkgId);

        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteIp(): string
    {
        return $this->getData('remote_ip');
    }

    /**
     * @param string $remoteIp
     * @return $this
     */
    public function setRemoteIp(string $remoteIp): SimpleReturnInterface
    {
        $this->setData('remote_ip', $remoteIp);

        return $this;
    }
}
