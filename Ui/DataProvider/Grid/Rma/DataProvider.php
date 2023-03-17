<?php
/**
 * DataProvider.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Ui\DataProvider\Grid\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Ui\DataProvider\Grid\Rma;

use AuroraExtensions\SimpleReturns\Model\Display\LabelManager;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\SimpleReturn\Collection;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\SimpleReturn\CollectionFactory;
use Countable;
use Magento\Framework\Api\Filter;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class DataProvider extends AbstractDataProvider implements
    Countable,
    DataProviderInterface
{
    /** @var AddFieldToCollectionInterface[] $addFieldStrategies */
    private $addFieldStrategies;

    /** @var AddFilterToCollectionInterface[] $addFilterStrategies */
    private $addFilterStrategies;

    /** @var LabelManager $labelManager */
    private $labelManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     * @param AddFieldToCollectionInterface[] $addFieldStrategies
     * @param AddFilterToCollectionInterface[] $addFilterStrategies
     * @param CollectionFactory $collectionFactory
     * @param LabelManager $labelManager
     * @return void
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = [],
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        CollectionFactory $collectionFactory,
        LabelManager $labelManager
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->collection = $collectionFactory->create();
        $this->viewModel = $viewModel;
        $this->labelManager = $labelManager;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        /** @var array $entries */
        $entries = $this->getCollection()->toArray();

        /** @var array $items */
        $items = $entries['items'] ?? [];

        /** @var array $data */
        $data = [
            'totalRecords' => $this->count(),
            'items' => [],
        ];

        /** @var array $item */
        foreach ($items as $item) {
            $data['items'][] = $this->labelManager->replace($item);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]
                 ->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Filter $filter)
    {
        /** @var string $field */
        $field = $filter->getField();

        if (isset($this->addFilterStrategies[$field])) {
            $this->addFilterStrategies[$field]
                 ->addFilter(
                     $this->getCollection(),
                     $field,
                     [$filter->getConditionType() => $filter->getValue()]
                 );
        } else {
            parent::addFilter($filter);
        }
    }
}
