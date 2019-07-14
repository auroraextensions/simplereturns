<?php
/**
 * LabelRepository.php
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

namespace AuroraExtensions\SimpleReturns\Model\RepositoryModel;

use AuroraExtensions\SimpleReturns\{
    Api\AbstractCollectionInterfaceFactory,
    Api\LabelRepositoryInterface,
    Api\Data\LabelInterface,
    Api\Data\LabelInterfaceFactory,
    Api\Data\LabelSearchResultsInterfaceFactory,
    Api\Data\PackageInterface,
    Exception\ExceptionFactory,
    Model\Label as LabelDataModel,
    Model\ResourceModel\Label as LabelResourceModel,
    Shared\ModuleComponentInterface
};

use Magento\Framework\{
    Api\SearchResultsInterface,
    Api\SearchResultsInterfaceFactory,
    Exception\NoSuchEntityException
};

class LabelRepository extends AbstractRepository implements
    LabelRepositoryInterface,
    ModuleComponentInterface
{
    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property LabelInterfaceFactory $labelFactory */
    protected $labelFactory;

    /** @property LabelResourceModel $labelResource */
    protected $labelResource;

    /**
     * @param AbstractCollectionInterfaceFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param LabelInterfaceFactory $labelFactory
     * @param LabelResourceModel $labelResource
     * @param ExceptionFactory $exceptionFactory
     * @return void
     */
    public function __construct(
        $collectionFactory,
        $searchResultsFactory,
        LabelInterfaceFactory $labelFactory,
        LabelResourceModel $labelResource,
        ExceptionFactory $exceptionFactory
    ) {
        parent::__construct(
            $collectionFactory,
            $searchResultsFactory
        );

        $this->labelFactory = $labelFactory;
        $this->labelResource = $labelResource;
        $this->exceptionFactory = $exceptionFactory;
    }

    /**
     * @param PackageInterface $package
     * @return LabelInterface
     * @throws NoSuchEntityException
     */
    public function get(PackageInterface $package): LabelInterface
    {
        /** @var LabelDataModel $label */
        $label = $this->labelFactory->create();
        $this->labelResource->load(
            $label,
            $package->getId(),
            self::SQL_COLUMN_PKG_PRIMARY_FIELD
        );

        if (!$label->getId()) {
            throw $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate label(s) for the requested package.')
            );
        }

        return $label;
    }

    /**
     * @param int $id
     * @return LabelInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $id): LabelInterface
    {
        /** @var LabelDataModel $label */
        $label = $this->labelFactory->create();
        $this->labelResource->load($label, $id);

        if (!$label->getId()) {
            throw $this->exceptionFactory->create(
                NoSuchEntityException::class,
                __('Unable to locate label(s) for the requested package.')
            );
        }

        return $label;
    }

    /**
     * @param LabelInterface $label
     * @return int
     */
    public function save(LabelInterface $label): int
    {
        $this->labelResource->save($label);
        return (int) $label->getId();
    }

    /**
     * @param LabelInterface $label
     * @return bool
     */
    public function delete(LabelInterface $label): bool
    {
        return $this->deleteById($label->getId());
    }

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        /** @var LabelDataModel $label */
        $label = $this->labelFactory->create();
        $label->setId($id);

        return (bool) $this->labelResource->delete($label);
    }
}
