<?php
/**
 * InfoView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ViewModel\Package\Form
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Package\Form;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface,
    Component\System\ModuleConfigTrait,
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

class InfoView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    use ModuleConfigTrait;

    /** @var DirectoryHelper $directoryHelper */
    protected $directoryHelper;

    /** @var MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @var OrderInterface $order */
    protected $order;

    /** @var OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @var SimpleReturnInterface $rma */
    protected $rma;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

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
        array $data = []
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
        return $this->getConfig()
            ->getShippingCarrier();
    }

    /**
     * @return string
     */
    public function getShippingMethod(): string
    {
        return $this->getConfig()
            ->getShippingMethod();
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
     * @return SimpleReturnInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSimpleReturn(): ?SimpleReturnInterface
    {
        /** @var int|string|null $rmaId */
        $rmaId = $this->request->getParam(static::PARAM_RMA_ID);
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        if ($rmaId !== null) {
            /** @var string|null $token */
            $token = $this->request->getParam(static::PARAM_TOKEN);
            $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

            if ($token !== null) {
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
        if ($this->order !== null) {
            return $this->order;
        }

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
                    $this->order = array_shift($orders);
                    return $this->order;
                }

                /** @var NoSuchEntityException $exception */
                $exception = $this->exceptionFactory->create(
                    NoSuchEntityException::class,
                    __('Unable to locate any matching orders.')
                );
                throw $exception;
            } catch (NoSuchEntityException | LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
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
     * @return string
     */
    public function getViewRmaUrl(): string
    {
        /** @var array $params */
        $params = ['_secure' => true];

        /** @var SimpleReturnInterface $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            $params += [
                'rma_id' => $rma->getId(),
                'token' => $rma->getToken(),
            ];
        }

        return $this->urlBuilder->getUrl(
            'simplereturns/rma/view',
            $params
        );
    }
}
