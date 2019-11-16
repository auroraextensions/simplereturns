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

use Countable;
use AuroraExtensions\SimpleReturns\{
    Component\Ui\DataProvider\Modifier\ModifierPoolTrait,
    Model\ResourceModel\SimpleReturn as SimpleReturnResource,
    Model\ResourceModel\SimpleReturn\Collection,
    Model\ResourceModel\SimpleReturn\CollectionFactory,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    Api\FilterBuilder,
    Api\Search\SearchCriteria,
    Api\Search\SearchCriteriaBuilder,
    Api\Search\SearchResultInterface,
    App\RequestInterface,
    View\Element\UiComponent\DataProvider\DataProviderInterface
};
use Magento\Ui\{
    DataProvider\AbstractDataProvider,
    DataProvider\Modifier\ModifierInterface,
    DataProvider\Modifier\PoolInterface
};

class DataProvider extends AbstractDataProvider implements
    Countable,
    DataProviderInterface,
    ModuleComponentInterface
{
    /**
     * @property PoolInterface $modifierPool
     * @method getModifierPool()
     */
    use ModifierPoolTrait;

    /** @constant string WILDCARD */
    public const WILDCARD = '*';

    /** @property FilterBuilder $filterBuilder */
    protected $filterBuilder;

    /** @property array $loadedData */
    protected $loadedData = [];

    /** @property RequestInterface $request */
    protected $request;

    /** @property SearchCriteriaBuilder $searchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     * @param CollectionFactory $collectionFactory
     * @param FilterBuilder $filterBuilder
     * @param PoolInterface $modifierPool
     * @param RequestInterface $request
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @return void
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = [],
        CollectionFactory $collectionFactory,
        FilterBuilder $filterBuilder,
        PoolInterface $modifierPool,
        RequestInterface $request,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        $this->filterBuilder = $filterBuilder;
        $this->modifierPool = $modifierPool;
        $this->request = $request;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->prepareSubmitUrl();
    }

    /**
     * @return void
     */
    public function prepareSubmitUrl(): void
    {
        if (isset($this->data['config']['submit_url'])) {
            $this->parseSubmitUrl();
        }

        if (isset($this->data['config']['filter_url_params'])) {
            /** @var string $paramName */
            /** @var mixed $paramValue */
            foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {
                $paramValue = $paramValue !== static::WILDCARD
                    ? $paramValue
                    : $this->request->getParam($paramName);

                if ($paramValue) {
                    $this->data['config']['submit_url'] = sprintf(
                        '%s%s/%s/',
                        $this->data['config']['submit_url'],
                        $paramName,
                        $paramValue
                    );

                    /** @var Filter $filter */
                    $filter = $this->filterBuilder
                        ->setField($paramName)
                        ->setValue($paramValue)
                        ->setConditionType('eq')
                        ->create();

                    $this->searchCriteriaBuilder->addFilter($filter);
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function parseSubmitUrl(): void
    {
        /** @var string $actionName */
        $actionName = strtolower($this->request->getActionName()) . 'Post';

        /** @var string $submitUrl */
        $submitUrl = $this->data['config']['submit_url'];

        $this->data['config']['submit_url'] = str_replace(
            ':action',
            $actionName,
            $submitUrl
        );
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        /** @var array $meta */
        $meta = parent::getMeta();

        /** @var PoolInterface $pool */
        $pool = $this->getModifierPool();

        /** @var ModifierInterface[] $modifiers */
        $modifiers = $pool->getModifiersInstances();

        /** @var ModifierInterface $modifier */
        foreach ($modifiers as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        /** @var SimpleReturnInterface[] $items */
        $items = $this->getCollection()->getItems();

        /** @var SimpleReturnInterface $rma */
        foreach ($items as $rma) {
            $this->loadedData[$rma->getId()] = $rma->getData();
        }

        /** @var PoolInterface $pool */
        $pool = $this->getModifierPool();

        /** @var ModifierInterface[] $modifiers */
        $modifiers = $pool->getModifiersInstances();

        /** @var ModifierInterface $modifier */
        foreach ($modifiers as $modifier) {
            $this->loadedData = $modifier->modifyData($this->loadedData);
        }

        return $this->loadedData;
    }
}
