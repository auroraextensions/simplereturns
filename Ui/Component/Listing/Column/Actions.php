<?php
/**
 * Actions.php
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

namespace AuroraExtensions\SimpleReturns\Ui\Component\Listing\Column;

use Magento\Framework\{
    UrlInterface,
    View\Element\UiComponent\ContextInterface,
    View\Element\UiComponentFactory
};
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    /** @property string $paramKey */
    protected $paramKey;

    /** @property UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory,
     * @param array $components
     * @param array $data
     * @param UrlInterface $urlBuilder
     * @param string $paramKey
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        UrlInterface $urlBuilder,
        string $paramKey = null
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->urlBuilder = $urlBuilder;
        $this->paramKey = $paramKey ?? 'entity_id';
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$this->paramKey])) {
                    /** @var string $viewUrlPath */
                    $viewUrlPath = $this->getData('config/viewUrlPath') ?? '#';

                    /** @var string $editUrlPath */
                    $editUrlPath = $this->getData('config/editUrlPath') ?? '#';

                    /** @var string $urlEntityParamName */
                    $urlEntityParamName = $this->getData('config/urlEntityParamName')
                        ?? $this->paramKey;

                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $this->urlBuilder->getUrl(
                                $viewUrlPath,
                                [
                                    $urlEntityParamName => $item[$this->paramKey],
                                ]
                            ),
                            'label' => __('View'),
                        ],
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                $editUrlPath,
                                [
                                    $urlEntityParamName => $item[$this->paramKey],
                                ]
                            ),
                            'label' => __('Edit'),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
