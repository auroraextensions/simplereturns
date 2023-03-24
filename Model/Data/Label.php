<?php
/**
 * Label.php
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
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Label as LabelResource;
use Magento\Framework\Model\AbstractModel;

class Label extends AbstractModel implements LabelInterface
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(LabelResource::class);
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
    public function setCreatedAt($createdAt): LabelInterface
    {
        $this->setData('created_at', $createdAt);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getImage(): ?string
    {
        return $this->getData('image');
    }

    /**
     * {@inheritdoc}
     */
    public function setImage(?string $image): LabelInterface
    {
        $this->setData('image', $image);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrackingNumber(): string
    {
        return $this->getData('tracking_number');
    }

    /**
     * {@inheritdoc}
     */
    public function setTrackingNumber(string $trackingNumber): LabelInterface
    {
        $this->setData('tracking_number', $trackingNumber);
        return $this;
    }
}
