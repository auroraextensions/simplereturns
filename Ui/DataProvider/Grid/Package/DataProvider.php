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
 * @package     AuroraExtensions\SimpleReturns\Ui\DataProvider\Grid\Package
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Ui\DataProvider\Grid\Package;

use AuroraExtensions\SimpleReturns\Model\Display\LabelManager;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Package as PackageResource;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Package\Collection;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Package\CollectionFactory;
use Countable;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider implements
    Countable,
    DataProviderInterface
{
    /** @var array $mapKeys */
    private $mapKeys = [
        'carriers' => 'carrier_code',
        'methods' => 'method_code',
    ];

    /** @var LabelManager $labelManager */
    private $labelManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
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
        CollectionFactory $collectionFactory
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        $this->labelManager = $labelManager;
    }

    /**
     * @return array
     */
    public function getData()
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
}
