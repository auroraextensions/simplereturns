<?php
/**
 * ViewView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ViewModel\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Rma;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Api\Data\LabelInterface;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\LabelRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\PackageRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Helper\Config as ConfigHelper;
use AuroraExtensions\SimpleReturns\Model\Display\LabelManager;
use AuroraExtensions\SimpleReturns\Model\Search\Attachment as AttachmentAdapter;
use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use AuroraExtensions\SimpleReturns\Model\SystemModel\Module\Config as ModuleConfig;
use AuroraExtensions\SimpleReturns\Model\ViewModel\AbstractView;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

use function count;
use function is_numeric;
use function rtrim;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewView extends AbstractView implements ArgumentInterface
{
    private const PARAM_RMA_ID = 'rma_id';
    private const PARAM_TOKEN = 'token';

    /** @var AttachmentAdapter $attachmentAdapter */
    private $attachmentAdapter;

    /** @var LabelInterface $label */
    private $label;

    /** @var LabelManager $labelManager */
    private $labelManager;

    /** @var LabelRepositoryInterface $labelRepository */
    private $labelRepository;

    /** @var MessageManagerInterface $messageManager */
    private $messageManager;

    /** @var OrderInterface $order */
    private $order;

    /** @var OrderRepositoryInterface $orderRepository */
    private $orderRepository;

    /** @var PackageInterface $package */
    private $package;

    /** @var PackageRepositoryInterface $packageRepository */
    private $packageRepository;

    /** @var SimpleReturnInterface $rma */
    private $rma;

    /** @var Json $serializer */
    private $serializer;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /** @var StoreManagerInterface $storeManager */
    private $storeManager;

    /** @var Tokenizer $tokenizer */
    private $tokenizer;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param AttachmentAdapter $attachmentAdapter
     * @param LabelManager $labelManager
     * @param LabelRepositoryInterface $labelRepository
     * @param MessageManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     * @param PackageRepositoryInterface $packageRepository
     * @param Json $serializer
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param StoreManagerInterface $storeManager
     * @param Tokenizer $tokenizer
     * @param array $data
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        AttachmentAdapter $attachmentAdapter,
        LabelManager $labelManager,
        LabelRepositoryInterface $labelRepository,
        MessageManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        PackageRepositoryInterface $packageRepository,
        Json $serializer,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        StoreManagerInterface $storeManager,
        Tokenizer $tokenizer,
        array $data = []
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );
        $this->attachmentAdapter = $attachmentAdapter;
        $this->labelManager = $labelManager;
        $this->labelRepository = $labelRepository;
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
        $this->packageRepository = $packageRepository;
        $this->serializer = $serializer;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->storeManager = $storeManager;
        $this->tokenizer = $tokenizer;
    }

    /**
     * @return string
     */
    public function getCreatePackageUrl(): string
    {
        /** @var array $params */
        $params = ['_secure' => true];

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
    public function getViewCustomerUrl(): string
    {
        /** @var array $params */
        $params = ['_secure' => true];

        /** @var OrderInterface|null $order */
        $order = $this->getOrder();

        if ($order !== null) {
            /** @var bool $isGuest */
            $isGuest = (bool) $order->getCustomerIsGuest();

            if (!$isGuest) {
                /** @var int|string|null $customerId */
                $customerId = $order->getCustomerId();

                if ($customerId !== null) {
                    $params['id'] = $customerId;
                }
            }
        }

        return $this->urlBuilder->getUrl(
            'customer/index/edit',
            $params
        );
    }

    /**
     * @return string
     */
    public function getViewPackageUrl(): string
    {
        /** @var array $params */
        $params = ['_secure' => true];

        /** @var PackageInterface|null $package */
        $package = $this->getPackage();

        if ($package !== null) {
            $params['pkg_id'] = $package->getId();
            $params['token'] = $package->getToken();
        }

        return $this->urlBuilder->getUrl(
            'simplereturns/package/view',
            $params
        );
    }

    /**
     * @return string
     */
    public function getEditRmaUrl(): string
    {
        /** @var array $params */
        $params = ['_secure' => true];

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
            'simplereturns/rma/edit',
            $params
        );
    }

    /**
     * Get frontend label for field type by key.
     *
     * @param string $type
     * @param string $key
     * @param string
     */
    public function getFrontLabel(
        string $type,
        string $key
    ): string {
        return $this->labelManager
            ->getLabel($type, $key) ?? $key;
    }

    /**
     * Get associated SimpleReturn data object.
     *
     * @return SimpleReturnInterface|null
     * @throws NoSuchEntityException
     */
    public function getSimpleReturn(): ?SimpleReturnInterface
    {
        if ($this->rma !== null) {
            return $this->rma;
        }

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

                $this->rma = $rma;
                return $rma;
            } catch (NoSuchEntityException | LocalizedException $e) {
                /* No action required. */
            }
        }

        return null;
    }

    /**
     * Get associated order.
     *
     * @return OrderInterface|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getOrder(): ?OrderInterface
    {
        if ($this->order !== null) {
            return $this->order;
        }

        /** @var SimpleReturnInterface $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            try {
                /** @var OrderInterface $order */
                $order = $this->orderRepository->get($rma->getOrderId());

                if ($order->getId()) {
                    $this->order = $order;
                    return $order;
                }
            } catch (NoSuchEntityException | LocalizedException $e) {
                /* No action required. */
            }
        }

        return null;
    }

    /**
     * @return PackageInterface|null
     */
    public function getPackage(): ?PackageInterface
    {
        if ($this->package !== null) {
            return $this->package;
        }

        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            /** @var int|string|null $pkgId */
            $pkgId = $rma->getPackageId();
            $pkgId = is_numeric($pkgId) ? (int) $pkgId : null;

            if ($pkgId !== null) {
                try {
                    /** @var PackageInterface $package */
                    $package = $this->packageRepository->getById($pkgId);

                    if ($package->getId()) {
                        $this->package = $package;
                        return $package;
                    }
                } catch (NoSuchEntityException | LocalizedException $e) {
                    /* No action required. */
                }
            }
        }

        return null;
    }

    /**
     * @return LabelInterface|null
     */
    public function getLabel(): ?LabelInterface
    {
        if ($this->label !== null) {
            return $this->label;
        }

        /** @var PackageInterface|null $package */
        $package = $this->getPackage();

        if ($package !== null) {
            try {
                /** @var LabelInterface $label */
                $label = $this->labelRepository->get($package);

                if ($label->getId()) {
                    $this->label = $label;
                    return $label;
                }
            } catch (NoSuchEntityException | LocalizedException $e) {
                /* No action required. */
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAttachments(): array
    {
        /** @var array $results */
        $results = [];

        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            /** @var string $baseUrl */
            $baseUrl = $this->storeManager
                ->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            $baseUrl = rtrim($baseUrl, '/');

            /** @var AttachmentInterface[] $attachments */
            $attachments = $this->attachmentAdapter
                ->getRecordsByFields(['rma_id' => $rma->getId()]);

            /** @var AttachmentInterface $attachment */
            foreach ($attachments as $attachment) {
                $results[] = ($baseUrl . $attachment->getThumbnail());
            }
        }

        return $results;
    }

    /**
     * @return bool
     */
    public function hasAttachments(): bool
    {
        return count($this->getAttachments()) > 0;
    }

    /**
     * @return bool
     */
    public function hasLabel(): bool
    {
        /** @var LabelInterface|null $label */
        $label = $this->getLabel();
        return $label !== null
            ? (bool) $label->getId() : false;
    }

    /**
     * @return bool
     */
    public function hasPackage(): bool
    {
        /** @var PackageInterface|null $package */
        $package = $this->getPackage();
        return $package !== null ? (bool) $package->getId() : false;
    }

    /**
     * @return bool
     */
    public function hasSimpleReturn(): bool
    {
        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();
        return $rma !== null ? (bool) $rma->getId() : false;
    }

    /**
     * @return bool
     */
    public function isRequestApproved(): bool
    {
        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            /** @var string $status */
            $status = $rma->getStatus()
                ?? ModuleConfig::DEFAULT_RMA_STATUS_CODE;
            return ($status === 'approved');
        }

        return false;
    }
}
