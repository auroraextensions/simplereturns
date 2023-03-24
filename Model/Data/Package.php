<?php
/**
 * Package.php
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

use AuroraExtensions\SimpleReturns\Api\Data\LabelInterface;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterface;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Package as PackageResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class Package extends AbstractModel implements PackageInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(PackageResource::class);
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
    public function setCreatedAt($createdAt): PackageInterface
    {
        $this->setData('created_at', $createdAt);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUuid(): string
    {
        return $this->getData('uuid');
    }

    /**
     * {@inheritdoc}
     */
    public function setUuid(string $uuid): PackageInterface
    {
        $this->setData('uuid', $uuid);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCarrier(): ?CarrierInterface
    {
        return $this->getData('carrier');
    }

    /**
     * {@inheritdoc}
     */
    public function setCarrier(CarrierInterface $carrier): PackageInterface
    {
        $this->setData('carrier', $carrier);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCarrierCode(): string
    {
        return $this->getData('carrier_code');
    }

    /**
     * {@inheritdoc}
     */
    public function setCarrierCode(string $code): PackageInterface
    {
        $this->setData('carrier_code', $code);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContainerType(): string
    {
        return $this->getData('container_type');
    }

    /**
     * {@inheritdoc}
     */
    public function setContainerType(string $type): PackageInterface
    {
        $this->setData('container_type', $type);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->getData('description');
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(string $description): PackageInterface
    {
        $this->setData('description', $description);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensionUnits(): string
    {
        return $this->getData('dimension_units');
    }

    /**
     * {@inheritdoc}
     */
    public function setDimensionUnits(string $units): PackageInterface
    {
        $this->setData('dimension_units', $units);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): LabelInterface
    {
        return $this->getData('label');
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel(LabelInterface $label): PackageInterface
    {
        $this->setData('label', $label);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeight(): float
    {
        return $this->getData('weight');
    }

    /**
     * {@inheritdoc}
     */
    public function setWeight(float $weight): PackageInterface
    {
        $this->setData('weight', $weight);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWeightUnits(): string
    {
        return $this->getData('weight_units');
    }

    /**
     * {@inheritdoc}
     */
    public function setWeightUnits(string $units): PackageInterface
    {
        $this->setData('weight_units', $units);
        return $this;
    }
}
