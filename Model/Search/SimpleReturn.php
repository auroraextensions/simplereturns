<?php
/**
 * SimpleReturn.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\Search
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\Search;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Throwable;

use function __;
use function array_values;

class SimpleReturn
{
    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var FilterBuilder $filterBuilder */
    private $filterBuilder;

    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

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

            if (empty($rmas)) {
                /** @var LocalizedException $exception */
                $exception = $this->exceptionFactory->create(
                    LocalizedException::class,
                    __('Unable to locate any matching RMAs.')
                );
                throw $exception;
            }

            return $rmas;
        } catch (Throwable $e) {
            return [];
        }
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
