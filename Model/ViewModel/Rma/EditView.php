<?php
/**
 * EditView.php
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
use AuroraExtensions\SimpleReturns\Api\AttachmentManagementInterface;
use AuroraExtensions\SimpleReturns\Api\AttachmentRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Helper\Config as ConfigHelper;
use AuroraExtensions\SimpleReturns\Model\Adapter\Sales\Order as OrderAdapter;
use AuroraExtensions\SimpleReturns\Model\Search\Attachment as AttachmentAdapter;
use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use AuroraExtensions\SimpleReturns\Model\SystemModel\Module\Config as ModuleConfig;
use AuroraExtensions\SimpleReturns\Model\ViewModel\AbstractView;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

use function is_numeric;

class EditView extends AbstractView implements ArgumentInterface
{
    private const PARAM_RMA_ID = 'rma_id';
    private const PARAM_TOKEN = 'token';
    private const ROUTE_PATH = 'simplereturns/rma/editPost';

    /** @var AttachmentAdapter $attachmentAdapter */
    private $attachmentAdapter;

    /** @var AttachmentManagementInterface $attachmentManagement */
    private $attachmentManagement;

    /** @var AttachmentRepositoryInterface $attachmentRepository */
    private $attachmentRepository;

    /** @var FormKey $formKey */
    private $formKey;

    /** @var MessageManagerInterface $messageManager */
    private $messageManager;

    /** @var ModuleConfig $moduleConfig */
    private $moduleConfig;

    /** @var OrderInterface $order */
    private $order;

    /** @var OrderAdapter $orderAdapter */
    private $orderAdapter;

    /** @var OrderRepositoryInterface $orderRepository */
    private $orderRepository;

    /** @var SimpleReturnInterface $rma */
    private $rma;

    /** @var Json $serializer */
    private $serializer;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /** @var StoreManagerInterface $storeManager */
    private $storeManager;

    /** @var string $route */
    private $route;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param AttachmentAdapter $attachmentAdapter
     * @param AttachmentManagementInterface $attachmentManagement
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param FormKey $formKey
     * @param MessageManagerInterface $messageManager
     * @param ModuleConfig $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param OrderRepositoryInterface $orderRepository
     * @param Json $serializer
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param StoreManagerInterface $storeManager
     * @param array $data
     * @param string $route
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        AttachmentAdapter $attachmentAdapter,
        AttachmentManagementInterface $attachmentManagement,
        AttachmentRepositoryInterface $attachmentRepository,
        FormKey $formKey,
        MessageManagerInterface $messageManager,
        ModuleConfig $moduleConfig,
        OrderAdapter $orderAdapter,
        OrderRepositoryInterface $orderRepository,
        Json $serializer,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        StoreManagerInterface $storeManager,
        array $data = [],
        string $route = self::ROUTE_PATH
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );
        $this->attachmentAdapter = $attachmentAdapter;
        $this->attachmentManagement = $attachmentManagement;
        $this->attachmentRepository = $attachmentRepository;
        $this->formKey = $formKey;
        $this->messageManager = $messageManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
        $this->orderRepository = $orderRepository;
        $this->serializer = $serializer;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->storeManager = $storeManager;
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

    /**
     * @return string
     */
    public function getViewRmaUrl(): string
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
            'simplereturns/rma/view',
            $params
        );
    }

    /**
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
     * @return OrderInterface|null
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
     * @return array
     */
    public function getFiles(): array
    {
        /** @var array $files */
        $files = [];

        /** @var SimpleReturnInterface|null $rma */
        $rma = $this->getSimpleReturn();

        if ($rma !== null) {
            try {
                /** @var AttachmentInterface[] $attachments */
                $attachments = $this->attachmentAdapter
                    ->getRecordsByFields(['rma_id' => $rma->getId()]);

                /** @var AttachmentInterface $attachment */
                foreach ($attachments as $attachment) {
                    /** @var string $dataUri */
                    $dataUri = $this->attachmentManagement
                        ->getFileDataUri($attachment);

                    /** @var string $fileUrl */
                    $fileUrl = $this->attachmentManagement
                        ->getFileUrl($attachment);

                    $files[] = [
                        'blob'  => $dataUri,
                        'name'  => $attachment->getFilename(),
                        'size'  => $attachment->getFilesize(),
                        'token' => $attachment->getToken(),
                        'type'  => $attachment->getMimeType(),
                        'url'   => $fileUrl,
                    ];
                }
            } catch (NoSuchEntityException | LocalizedException $e) {
                /* No action required. */
            }
        }

        return $files;
    }

    /**
     * @return string
     */
    public function getSerializedFiles(): string
    {
        return $this->serializer->serialize($this->getFiles());
    }

    /**
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }

    /**
     * @return array
     */
    public function getReasons(): array
    {
        return $this->moduleConfig->getReasons();
    }

    /**
     * @return array
     */
    public function getResolutions(): array
    {
        return $this->moduleConfig->getResolutions();
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
}
