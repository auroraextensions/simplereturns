<?php
/**
 * PackageInterface.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package      AuroraExtensions\SimpleReturns\Api\Data
 * @copyright    Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license      MIT
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
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string|\DateTime $createdAt
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUuid(): string;

    /**
     * @param string $uuid
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     */
    public function setUuid(string $uuid): PackageInterface;

    /**
     * @return CarrierInterface|null
     */
    public function getCarrier(): ?CarrierInterface;

    /**
     * @param CarrierInterface $carrier
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     */
    public function setCarrier(CarrierInterface $carrier): PackageInterface;

    /**
     * @return string
     */
    public function getCarrierCode(): string;

    /**
     * @param string $code
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     */
    public function setCarrierCode(string $code): PackageInterface;

    /**
     * @return string
     */
    public function getContainerType(): string;

    /**
     * @param string $type
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     */
    public function setContainerType(string $type): PackageInterface;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $description
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     */
    public function setDescription(string $description): PackageInterface;

    /**
     * @return string
     */
    public function getDimensionUnits(): string;

    /**
     * @param string $units
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     */
    public function setDimensionUnits(string $units): PackageInterface;

    /**
     * @return int|null
     */
    public function getLabelId(): ?int;

    /**
     * @param int|null $labelId
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     */
    public function setLabelId(?int $labelId): PackageInterface;

    /**
     * @return float
     */
    public function getWeight(): float;

    /**
     * @param float $weight
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     */
    public function setWeight(float $weight): PackageInterface;

    /**
     * @return string
     */
    public function getWeightUnits(): string;

    /**
     * @param string $units
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     */
    public function setWeightUnits(string $units): PackageInterface;
}
