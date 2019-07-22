<?php
/**
 * PackageManagement.php
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

namespace AuroraExtensions\SimpleReturns\Model\ManagementModel;

use AuroraExtensions\SimpleReturns\{
    Api\PackageManagementInterface,
    Api\Data\LabelInterface,
    Api\Data\LabelInterfaceFactory,
    Api\Data\PackageInterface,
    Api\Data\SimpleReturnInterface,
    Api\Data\SimpleReturnInterfaceFactory,
    Api\SimpleReturnRepositoryInterface,
    Shared\ModuleComponentInterface
};

class PackageManagement implements PackageManagementInterface, ModuleComponentInterface
{
    /** @property LabelInterfaceFactory $labelFactory */
    protected $labelFactory;

    /** @property SimpleReturnInterfaceFactory $simpleReturnFactory */
    protected $simpleReturnFactory;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /**
     * @param LabelInterfaceFactory $labelFactory
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @return void
     */
    public function __construct(
        LabelInterfaceFactory $labelFactory,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository
    ) {
        $this->labelFactory = $labelFactory;
        $this->simpleReturnFactory = $simpleReturnFactory;
        $this->simpleReturnRepository = $simpleReturnRepository;
    }

    /**
     * Create package label.
     *
     * @param PackageInterface $package
     * @return LabelInterface|null
     */
    public function createLabel(PackageInterface $package): ?LabelInterface
    {
        /** @var LabelInterface $label */
        $label = $this->labelFactory->create();

        /** @var SimpleReturnInterface $rma */
        $rma = $this->simpleReturnFactory->create();

        return $label;
    }
}
