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
 * @package     AuroraExtensions\SimpleReturns\Ui\DataProvider\Form\Package
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Ui\DataProvider\Form\Package;

use AuroraExtensions\SimpleReturns\Api\Data\PackageInterface;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Package as PackageResource;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Package\Collection;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Package\CollectionFactory;
use Countable;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

use function sprintf;
use function str_replace;
use function strtolower;

class DataProvider extends AbstractDataProvider implements
    Countable,
    DataProviderInterface
{
    public const WILDCARD = '*';

    /** @var array $cache */
    private $cache = [];

    /** @var FilterBuilder $filterBuilder */
    private $filterBuilder;

    /** @var RequestInterface $request */
    private $request;

    /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     * @param CollectionFactory $collectionFactory
     * @param FilterBuilder $filterBuilder
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
        $this->request = $request;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->prepareSubmitUrl();
    }

    /**
     * @return void
     */
    private function prepareSubmitUrl(): void
    {
        if (isset($this->data['config']['submit_url'])) {
            $this->parseSubmitUrl();
        }

        if (isset($this->data['config']['filter_url_params'])) {
            /** @var string $paramName */
            /** @var mixed $paramValue */
            foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {
                $paramValue = $paramValue !== static::WILDCARD
                    ? $paramValue : $this->request->getParam($paramName);

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
    private function parseSubmitUrl(): void
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
    public function getData(): array
    {
        if (!empty($this->cache)) {
            return $this->cache;
        }

        /** @var PackageInterface[] $items */
        $items = $this->getCollection()->getItems();

        /** @var PackageInterface $pkg */
        foreach ($items as $pkg) {
            $this->cache[$pkg->getId()] = $pkg->getData();
        }

        return $this->cache;
    }
}
