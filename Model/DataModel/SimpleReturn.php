<?php
/**
 * SimpleReturn.php
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

namespace AuroraExtensions\SimpleReturns\Model\DataModel;

use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
    Model\ResourceModel\SimpleReturn as SimpleReturnResourceModel,
    Shared\ModuleComponentInterface
};

use Magento\{
    Framework\Model\AbstractModel,
    Sales\Api\Data\OrderInterface
};

class SimpleReturn extends AbstractModel implements
    SimpleReturnInterface,
    ModuleComponentInterface
{
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
     * @return OrderInterface
     */
    public function getOrder(): OrderInterface
    {
        return $this->getData('order');
    }

    /**
     * @param OrderInterface $order
     * @return $this
     */
    public function setOrder(OrderInterface $order): SimpleReturnInterface
    {
        $this->setData('order', $order);

        return $this;
    }

    /**
     * @return PackageInterface[]
     */
    public function getPackages(): array
    {
        return $this->getData('packages');
    }

    /**
     * @param PackageInterface[] $packages
     * @return $this
     */
    public function setPackages(
        array $packages = []
    ): SimpleReturnInterface
    {
        $this->setData('packages', $packages);

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
