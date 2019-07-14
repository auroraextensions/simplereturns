<?php
/**
 * ViewView.php
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

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Rma;

use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Helper\Config as ConfigHelper,
    Model\AdapterModel\Security\Token as Tokenizer,
    Model\SystemModel\Module\Config as ModuleConfig,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    Message\ManagerInterface as MessageManagerInterface,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};
use Magento\Sales\{
    Api\Data\OrderInterface,
    Api\OrderRepositoryInterface
};

class ViewView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @property MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @property Tokenizer $tokenizer */
    protected $tokenizer;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param MessageManagerInterface $messageManager
     * @param ModuleConfig $moduleConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param Tokenizer $tokenizer
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = [],
        MessageManagerInterface $messageManager,
        ModuleConfig $moduleConfig,
        OrderRepositoryInterface $orderRepository,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        Tokenizer $tokenizer
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );

        $this->messageManager = $messageManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderRepository = $orderRepository;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->tokenizer = $tokenizer;
    }

    /**
     * @return string
     */
    public function getCreatePackageUrl(): string
    {
        /** @var array $params */
        $params = [
            '_secure' => true,
        ];

        /** @var int|string|null $rmaId */
        $rmaId = $this->request->getParam(self::PARAM_RMA_ID);

        if ($rmaId !== null) {
            $params['rma_id'] = $rmaId;
        }

        /** @var string|null $token */
        $token = $this->request->getParam(self::PARAM_TOKEN);

        if ($token !== null) {
            $params['token'] = $token;
        }

        return $this->urlBuilder->getUrl(
            'simplereturns/package/create',
            $params
        );
    }

    /**
     * @return string
     */
    public function getViewLabelUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'simplereturns/label/view',
            [
                '_secure' => true,
            ]
        );
    }

    /**
     * Get frontend label for field type by key.
     *
     * @param string $type
     * @param string $key
     * @param string
     */
    public function getFrontendLabel(string $type, string $key): string
    {
        /** @var array $labels */
        $labels = $this->moduleConfig->getSettings()->getData($type);

        return $labels[$key] ?? $key;
    }

    /**
     * Get associated order.
     *
     * @return OrderInterface|null
     * @throws NoSuchEntityException
     */
    public function getOrder(): ?OrderInterface
    {
        /** @var SimpleReturnInterface $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            try {
                return $this->orderRepository->get($rma->getOrderId());
            } catch (NoSuchEntityException $e) {
                /* No action required. */
            }
        }

        return null;
    }

    /**
     * Get associated SimpleReturn data object.
     *
     * @return SimpleReturnInterface|null
     * @throws NoSuchEntityException
     */
    public function getSimpleReturn(): ?SimpleReturnInterface
    {
        /** @var int|string|null $rmaId */
        $rmaId = $this->request->getParam(self::PARAM_RMA_ID);
        $rmaId = $rmaId !== null && is_numeric($rmaId)
            ? (int) $rmaId
            : null;

        if (is_int($rmaId)) {
            /** @var string|null $token */
            $token = $this->request->getParam(self::PARAM_TOKEN);
            $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

            if ($token !== null) {
                try {
                    /** @var SimpleReturnInterface $rma */
                    $rma = $this->simpleReturnRepository->getById($rmaId);

                    if (Tokenizer::isEqual($token, $rma->getToken())) {
                        return $rma;
                    }

                    /** @var LocalizedException $exception */
                    $exception = $this->exceptionFactory->create(
                        LocalizedException::class
                    );

                    throw $exception;
                } catch (NoSuchEntityException $e) {
                    /* No action required. */
                } catch (LocalizedException $e) {
                    /* No action required. */
                }
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasSimpleReturn(): bool
    {
        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            return true;
        }

        return false;
    }
}
