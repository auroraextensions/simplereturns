<?php
/**
 * Label.php
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
    Api\Data\LabelInterface,
    Model\ResourceModel\Label as LabelResourceModel,
    Shared\ModuleComponentInterface
};
use Magento\Framework\Model\AbstractModel;

class Label extends AbstractModel implements
    LabelInterface,
    ModuleComponentInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(LabelResourceModel::class);
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
    public function setCreatedAt($createdAt): LabelInterface
    {
        $this->setData('created_at', $createdAt);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->getData('image');
    }

    /**
     * @param string|null $image
     * @return $this
     */
    public function setImage(?string $image): LabelInterface
    {
        $this->setData('image', $image);

        return $this;
    }

    /**
     * @return string
     */
    public function getTrackingNumber(): string
    {
        return $this->getData('tracking_number');
    }

    /**
     * @param string $trackingNumber
     * @return $this
     */
    public function setTrackingNumber(string $trackingNumber): LabelInterface
    {
        $this->setData('tracking_number', $trackingNumber);

        return $this;
    }
}
