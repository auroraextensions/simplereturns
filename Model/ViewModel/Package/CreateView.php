<?php
/**
 * CreateView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ViewModel\Package
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Package;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface,
    Component\System\ModuleConfigTrait,
    Helper\Action as ActionHelper,
    Helper\Config as ConfigHelper,
    Model\AdapterModel\Sales\Order as OrderAdapter,
    Model\Security\Token as Tokenizer,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface,
    Csi\System\Module\ConfigInterface
};
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\{
    App\RequestInterface,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    Message\ManagerInterface as MessageManagerInterface,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};
use Magento\Sales\Api\Data\OrderInterface;

use function __;
use function array_shift;
use function is_array;
use function is_numeric;
use function number_format;

class CreateView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    use ModuleConfigTrait;

    /** @var DirectoryHelper $directoryHelper */
    protected $directoryHelper;

    /** @var MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @var OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @var string $route */
    private $route;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param DirectoryHelper $directoryHelper
     * @param MessageManagerInterface $messageManager
     * @param ConfigInterface $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param array $data
     * @param string $route
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        DirectoryHelper $directoryHelper,
        MessageManagerInterface $messageManager,
        ConfigInterface $moduleConfig,
        OrderAdapter $orderAdapter,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        array $data = [],
        string $route = self::ROUTE_SIMPLERETURNS_PKG_CREATEPOST
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );
        $this->directoryHelper = $directoryHelper;
        $this->messageManager = $messageManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
        $this->simpleReturnRepository = $simpleReturnRepository;
    }

    /**
     * @return string
     */
    public function getShippingCarrier(): string
    {
        return $this->getConfig()->getShippingCarrier();
    }

    /**
     * @return string
     */
    public function getShippingMethod(): string
    {
        return $this->getConfig()->getShippingMethod();
    }

    /**
     * Get frontend label for field type by key(s).
     *
     * @param string $type
     * @param string $key
     * @param string|null $subkey
     * @param string
     */
    public function getFrontLabel(
        string $type,
        string $key,
        string $subkey = null
    ): string {
        /** @var array $labels */
        $labels = $this->getConfig()
            ->getSettings()
            ->getData($type);

        /** @var string|array $label */
        $label = $labels[$key] ?? $key;

        if ($subkey !== null) {
            $label = is_array($label) && isset($label[$subkey])
                ? $label[$subkey] : $label;
        }

        return $label;
    }

    /**
     * Get associated SimpleReturn data object.
     *
     * @return SimpleReturnInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSimpleReturn(): ?SimpleReturnInterface
    {
        /** @var int|string|null $rmaId */
        $rmaId = $this->request->getParam(self::PARAM_RMA_ID);
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        /** @var string|null $token */
        $token = $this->request->getParam(self::PARAM_TOKEN);
        $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

        if ($rmaId !== null && $token !== null) {
            try {
                /** @var SimpleReturnInterface $rma */
                $rma = $this->simpleReturnRepository->getById($rmaId);

                if (!Tokenizer::isEqual($token, $rma->getToken())) {
                    /** @var LocalizedException $exception */
                    $exception = $this->exceptionFactory->create(
                        LocalizedException::class
                    );
                    throw $exception;
                }

                return $rma;
            } catch (NoSuchEntityException | LocalizedException $e) {
                /* No action required. */
            }
        }

        return null;
    }

    /**
     * @return OrderInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getOrder(): ?OrderInterface
    {
        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            /** @var array $fields */
            $fields = [
                'entity_id' => $rma->getOrderId(),
            ];

            try {
                /** @var OrderInterface[] $orders */
                $orders = $this->orderAdapter->getOrdersByFields($fields);

                if (!empty($orders)) {
                    return array_shift($orders);
                }

                /** @var NoSuchEntityException $exception */
                $exception = $this->exceptionFactory->create(
                    NoSuchEntityException::class,
                    __('Unable to locate any matching orders.')
                );
                throw $exception;
            } catch (NoSuchEntityException | LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        return null;
    }

    /**
     * @return string
     */
    public function getPackageWeight(): string
    {
        /** @var OrderInterface $order */
        $order = $this->getOrder();

        /** @var float $weight */
        $weight = (float)(
            $order->getWeight()
                ?? $this->getConfig()->getPackageWeight()
        );
        return number_format($weight, 2);
    }

    /**
     * @return string
     */
    public function getWeightUnits(): string
    {
        return $this->directoryHelper->getWeightUnit();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPostActionUrl(
        string $route = '',
        array $params = []
    ): string {
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

        return parent::getPostActionUrl($this->route, $params);
    }
}
