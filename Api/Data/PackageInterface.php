<?php
/**
 * PackageInterface.php
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

use Magento\Shipping\Model\Carrier\CarrierInterface;

interface PackageInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return CarrierInterface
     */
    public function getCarrier(): CarrierInterface;

    /**
     * @param CarrierInterface $carrier
     * @return PackageInterface
     */
    public function setCarrier(CarrierInterface $carrier): PackageInterface;

    /**
     * @return string
     */
    public function getCarrierCode(): string;

    /**
     * @param string $code
     * @return PackageInterface
     */
    public function setCarrierCode(string $code): PackageInterface;

    /**
     * @return string
     */
    public function getContainerType(): string;

    /**
     * @param string $type
     * @return PackageInterface
     */
    public function setContainerType(string $type): PackageInterface;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     * @return PackageInterface
     */
    public function setDescription(string $description): PackageInterface;

    /**
     * @return string
     */
    public function getDimensionUnits(): string;

    /**
     * @param string $units
     * @return PackageInterface
     */
    public function setDimensionUnits(string $units): PackageInterface;

    /**
     * @return LabelInterface
     */
    public function getLabel(): LabelInterface;

    /**
     * @param LabelInterface $label
     * @return PackageInterface
     */
    public function setLabel(LabelInterface $label): PackageInterface;

    /**
     * @return float
     */
    public function getWeight(): float;

    /**
     * @param float $weight
     * @return PackageInterface
     */
    public function setWeight(float $weight): PackageInterface;

    /**
     * @return string
     */
    public function getWeightUnits(): string;

    /**
     * @param string $units
     * @return PackageInterface
     */
    public function setWeightUnits(string $units): PackageInterface;
}
