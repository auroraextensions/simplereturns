<?php
/**
 * PackageRepository.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\RepositoryModel
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\RepositoryModel;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Api\AbstractCollectionInterfaceFactory,
    Api\PackageRepositoryInterface,
    Api\Data\PackageInterface,
    Api\Data\PackageInterfaceFactory,
    Api\Data\PackageSearchResultsInterfaceFactory,
    Api\Data\SimpleReturnInterface,
    Model\Package as PackageDataModel,
    Model\ResourceModel\Package as PackageResourceModel,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    Api\SearchResultsInterface,
    Api\SearchResultsInterfaceFactory,
    Exception\NoSuchEntityException
};

use function __;

class PackageRepository extends AbstractRepository implements
    PackageRepositoryInterface,
    ModuleComponentInterface
{
    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var PackageInterfaceFactory $packageFactory */
    protected $packageFactory;

    /** @var PackageResourceModel $packageResource */
    protected $packageResource;

    /**
     * @param AbstractCollectionInterfaceFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param PackageInterfaceFactory $packageFactory
     * @param PackageResourceModel $packageResource
     * @param ExceptionFactory $exceptionFactory
     * @return void
     */
    public function __construct(
        $collectionFactory,
        $searchResultsFactory,
        PackageInterfaceFactory $packageFactory,
        PackageResourceModel $packageResource,
        ExceptionFactory $exceptionFactory
    ) {
        parent::__construct(
            $collectionFactory,
            $searchResultsFactory
        );
        $this->packageFactory = $packageFactory;
        $this->packageResource = $packageResource;
        $this->exceptionFactory = $exceptionFactory;
    }

    /**
     * @param SimpleReturnInterface $rma
     * @return PackageInterface
     * @throws NoSuchEntityException
     */
    public function get(SimpleReturnInterface $rma): PackageInterface
    {
        /** @var PackageDataModel $package */
        $package = $this->packageFactory->create();
        $this->packageResource->load(
            $package,
            $rma->getId(),
            self::SQL_COLUMN_RMA_PRIMARY_FIELD
        );

        if (!$package->getId()) {
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate package(s) for the requested RMA.')
            );
            throw $exception;
        }

        return $package;
    }

    /**
     * @param int $id
     * @return PackageInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): PackageInterface
    {
        /** @var PackageDataModel $package */
        $package = $this->packageFactory->create();
        $this->packageResource->load($package, $id);

        if (!$package->getId()) {
            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate package(s) for the requested RMA.')
            );
            throw $exception;
        }

        return $package;
    }

    /**
     * @param PackageInterface $package
     * @return int
     */
    public function save(PackageInterface $package): int
    {
        $this->packageResource->save($package);
        return (int) $package->getId();
    }

    /**
     * @param PackageInterface $package
     * @return bool
     */
    public function delete(PackageInterface $package): bool
    {
        return $this->deleteById((int) $package->getId());
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        /** @var PackageDataModel $package */
        $package = $this->packageFactory->create();
        $package->setId($id);
        return (bool) $this->packageResource->delete($package);
    }
}
