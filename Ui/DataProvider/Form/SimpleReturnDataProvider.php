<?php
/**
 * SimpleReturnDataProvider.php
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

namespace AuroraExtensions\SimpleReturns\Ui\DataProvider\Form;

use AuroraExtensions\SimpleReturns\{
    Model\ResourceModel\SimpleReturn as SimpleReturnResource,
    Model\ResourceModel\SimpleReturn\Collection,
    Model\ResourceModel\SimpleReturn\CollectionFactory,
    Model\ViewModel\Rma\ListView as ViewModel,
    Shared\ModuleComponentInterface
};
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class SimpleReturnDataProvider extends AbstractDataProvider implements
    \Countable,
    DataProviderInterface,
    ModuleComponentInterface
{
    /** @property array $loadedData */
    protected $loadedData = [];

    /** @property ViewModel $viewModel */
    protected $viewModel;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     * @param CollectionFactory $collectionFactory
     * @param ViewModel $viewModel
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
        ViewModel $viewModel,
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
        $this->viewModel = $viewModel;
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
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        /** @var SimpleReturnInterface[] $entries */
        $entries = $this->getCollection()->toArray();

        /** @var array $items */
        $items = $entries['items'] ?? [];

        /** @var array $keys */
        $keys = $this->getLabelKeys();

        /** @var array $labels */
        $labels = $this->getLabels();

        $this->loadedData = [
            'totalRecords' => $this->count(),
            'items' => [],
        ];

        /** @var SimpleReturnInterface $rma */
        foreach ($items as $item) {
            /** @var string $key */
            foreach ($keys as $key) {
                /** @var string|null $labelValue */
                $labelValue = $item[$key] ?? null;

                if ($labelValue !== null) {
                    /** @var string|null $labelKey */
                    $labelKey = $labels[$key] ?? null;

                    if ($labelKey !== null) {
                        $item[$key] = $this->viewModel->getFrontLabel(
                            $labelKey,
                            $labelValue
                        );
                    }
                }
            }

            $this->loadedData['items'][] = $item;
        }

        return $this->loadedData;
    }
}
