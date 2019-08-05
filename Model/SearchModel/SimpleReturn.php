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

namespace AuroraExtensions\SimpleReturns\Model\SearchModel;

use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    Api\FilterBuilder,
    Api\SearchCriteriaBuilder,
    Exception\LocalizedException,
    Exception\NoSuchEntityException
};

class SimpleReturn implements ModuleComponentInterface
{
    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property FilterBuilder $filterBuilder */
    protected $filterBuilder;

    /** @property SearchCriteriaBuilder $searchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /**
     * @param ExceptionFactory $exceptionFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @return void
     */
    public function __construct(
        ExceptionFactory $exceptionFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SimpleReturnRepositoryInterface $simpleReturnRepository
    ) {
        $this->exceptionFactory = $exceptionFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->simpleReturnRepository = $simpleReturnRepository;
    }

    /**
     * @param array $fields
     * @return SimpleReturnInterface[]
     */
    public function getSimpleReturnsByFields(array $fields = []): array
    {
        /** @var array $filters */
        $filters = [];

        /** @var string $field */
        /** @var mixed $value */
        foreach ($fields as $field => $value) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setValue($value)
                ->create();
        }

        try {
            /** @var SimpleReturnInterface[] $rmas */
            $rmas = $this->getSimpleReturnsByFilters($filters);

            if (!empty($rmas)) {
                return $rmas;
            }

            /** @var NoSuchEntityException $exception */
            $exception = $this->exceptionFactory->create(
                LocalizedException::class,
                __('Unable to locate any matching RMAs.')
            );

            throw $exception;
        } catch (NoSuchEntityException $e) {
            /* No action required. */
        } catch (LocalizedException $e) {
            /* No action required. */
        }

        return [];
    }

    /**
     * @param array $filters
     * @return array
     */
    public function getSimpleReturnsByFilters(array $filters = []): array
    {
        /** @var SearchCriteria $criteria */
        $criteria = $this->searchCriteriaBuilder
            ->addFilters($filters)
            ->create();

        /** @var SimpleReturnInterface[] $items */
        $items = $this->simpleReturnRepository
            ->getList($criteria)
            ->getItems();

        return array_values($items);
    }
}
