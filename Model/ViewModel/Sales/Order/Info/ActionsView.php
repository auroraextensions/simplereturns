<?php
/**
 * ActionsView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ViewModel\Sales\Order\Info
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Sales\Order\Info;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface,
    Helper\Config as ConfigHelper,
    Model\ValidatorModel\Sales\Order\EligibilityValidator,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    Exception\NoSuchEntityException,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};
use Magento\Sales\Api\Data\OrderInterface;

class ActionsView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @var EligibilityValidator $validator */
    protected $validator;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param EligibilityValidator $validator
     * @param array $data
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        EligibilityValidator $validator,
        array $data = []
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->validator = $validator;
    }

    /**
     * @param OrderInterface $order
     * @return SimpleReturnInterface|null
     */
    public function getSimpleReturn(OrderInterface $order): ?SimpleReturnInterface
    {
        try {
            /** @var SimpleReturnInterface $rma */
            $rma = $this->simpleReturnRepository->get($order);
            return $rma->getId() ? $rma : null;
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function hasSimpleReturn(OrderInterface $order): bool
    {
        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn($order);
        return $rma !== null ? (bool) $rma->getId() : false;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getRmaCreateUrl(OrderInterface $order): string
    {
        return $this->urlBuilder->getUrl(
            'simplereturns/rma/create',
            [
                'order_id' => $order->getRealOrderId(),
                'code'     => $order->getProtectCode(),
                '_secure'  => true,
            ]
        );
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getRmaViewUrl(OrderInterface $order): string
    {
        /** @var SimpleReturnInterface $rma */
        $rma = $this->getSimpleReturn($order);

        return $this->urlBuilder->getUrl(
            'simplereturns/rma/view',
            [
                'rma_id'  => $rma->getId(),
                'token'   => $rma->getToken(),
                '_secure' => true,
            ]
        );
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isOrderEligible(OrderInterface $order): bool
    {
        return $this->validator->isOrderEligible($order);
    }
}
