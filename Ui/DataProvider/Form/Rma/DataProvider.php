<?php
/**
 * DataProvider.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Ui\DataProvider\Form\Rma;

use AuroraExtensions\SimpleReturns\{
    Model\ResourceModel\SimpleReturn as SimpleReturnResource,
    Model\ResourceModel\SimpleReturn\Collection,
    Model\ResourceModel\SimpleReturn\CollectionFactory,
    Shared\ModuleComponentInterface
};
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider implements
    \Countable,
    DataProviderInterface,
    ModuleComponentInterface
{
    /** @property array $loadedData */
    protected $loadedData = [];

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     * @param CollectionFactory $collectionFactory
     * @param array $labels
     * @return void
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = [],
        CollectionFactory $collectionFactory,
        array $labels = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        $this->labels = $labels;
    }

    /**
     * @return array
     */
    public function getLabelKeys(): array
    {
        /** @var array $labels */
        $labels = $this->labels ?? [];

        return array_keys($labels);
    }

    /**
     * @param bool $preserveKeys
     * @return array
     */
    public function getLabels(bool $preserveKeys = true): array
    {
        /** @var array $labels */
        $labels = $this->labels ?? [];

        return $preserveKeys ? $labels : array_values($labels);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return [];

        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        /** @var SimpleReturnInterface[] $items */
        $items = $this->getCollection()->getItems();

        /** @var SimpleReturnInterface $rma */
        foreach ($items as $rma) {
            $this->loadedData[$rma->getId()]['rma'] = $rma->getData();
        }

        return $this->loadedData;
    }
}
