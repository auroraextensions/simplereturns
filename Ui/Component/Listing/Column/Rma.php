<?php
/**
 * Rma.php
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

namespace AuroraExtensions\SimpleReturns\Ui\Component\Listing\Column;

use Exception;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use Magento\Framework\{
    Exception\NoSuchEntityException,
    UrlInterface,
    View\Element\UiComponent\ContextInterface,
    View\Element\UiComponentFactory
};
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Rma extends Column
{
    /** @constant string ACTION_KEY */
    public const ACTION_KEY = 'select';

    /** @constant string ACTION_LABEL */
    public const ACTION_LABEL = 'Select';

    /** @constant string ENTITY_KEY */
    public const ENTITY_KEY = 'rma_id';

    /** @constant string PARAM_KEY */
    public const PARAM_KEY = 'rma_id';

    /** @constant string TOKEN_KEY */
    public const TOKEN_KEY = 'token';

    /** @property string $entityKey */
    protected $entityKey;

    /** @property OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @property string $paramKey */
    protected $paramKey;

    /** @property string $tokenKey */
    protected $tokenKey;

    /** @property UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory,
     * @param array $components
     * @param array $data
     * @param OrderRepositoryInterface $orderRepository
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param UrlInterface $urlBuilder
     * @param string|null $entityKey
     * @param string|null $paramKey
     * @param string|null $tokenKey
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        OrderRepositoryInterface $orderRepository,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder,
        string $entityKey = null,
        string $paramKey = null,
        string $tokenKey = null
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->orderRepository = $orderRepository;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->urlBuilder = $urlBuilder;
        $this->entityKey = $entityKey ?? static::ENTITY_KEY;
        $this->paramKey = $paramKey ?? static::PARAM_KEY;
        $this->tokenKey = $tokenKey ?? static::TOKEN_KEY;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            /** @var array $item */
            foreach ($dataSource['data']['items'] as &$item) {
                /** @var string $entityKey */
                $entityKey = $this->getData('config/entityKey')
                    ?? $this->entityKey;

                if (isset($item[$entityKey])) {
                    /** @var string $actionPath */
                    $actionPath = $this->getData('config/actionPath') ?? '#';

                    /** @var string $paramKey */
                    $paramKey = $this->getData('config/paramKey')
                        ?? $this->paramKey;

                    /** @var string $secretKey */
                    $secretKey = $this->getData('config/secretKey')
                        ?? $this->tokenKey;

                    $item[$this->getData('name')] = [
                        static::ACTION_KEY => [
                            'href' => $this->urlBuilder->getUrl(
                                $actionPath,
                                [
                                    $paramKey => $item[$entityKey],
                                    $secretKey => $this->getSecret((int) $item[$entityKey]),
                                ]
                            ),
                            'label' => __(static::ACTION_LABEL),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param int $orderId
     * @return string|null
     */
    protected function getSecret(int $orderId): ?string
    {
        try {
            /** @var OrderInterface $order */
            $order = $this->orderRepository->get($orderId);

            /** @var SimpleReturnInterface $rma */
            $rma = $this->simpleReturnRepository->get($order);

            return $rma->getToken();
        } catch (NoSuchEntityException $e) {
            /* No action required. */
        } catch (Exception $e) {
            /* No action required. */
        }

        return null;
    }
}
