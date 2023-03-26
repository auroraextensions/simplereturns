<?php
/**
 * EditPost.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Controller\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Rma;

use AuroraExtensions\ModuleComponents\Component\Event\EventManagerTrait;
use AuroraExtensions\ModuleComponents\Component\Http\Request\RedirectTrait;
use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Api\AttachmentRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\Data\AttachmentInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Component\System\ModuleConfigTrait;
use AuroraExtensions\SimpleReturns\Csi\System\Module\ConfigInterface;
use AuroraExtensions\SimpleReturns\Model\Adapter\Sales\Order as OrderAdapter;
use AuroraExtensions\SimpleReturns\Model\Display\LabelManager;
use AuroraExtensions\SimpleReturns\Model\Email\Transport\Customer as EmailTransport;
use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Throwable;

use function array_shift;
use function is_numeric;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends Action implements HttpPostActionInterface
{
    /**
     * @var EventManagerInterface $eventManager
     * @method void dispatchEvent()
     * @method void dispatchEvents()
     * ---
     * @var ConfigInterface $moduleConfig
     * @method ConfigInterface getConfig()
     * ---
     * @method Redirect getRedirect()
     * @method Redirect getRedirectToPath()
     * @method Redirect getRedirectToUrl()
     */
    use EventManagerTrait,
        ModuleConfigTrait,
        RedirectTrait;

    private const DATA_GROUP_KEY = 'simplereturns_group_key';
    private const PARAM_RMA_ID = 'rma_id';
    private const PARAM_TOKEN = 'token';
    private const ROUTE_PATH = 'sales/guest/view';

    /** @var AttachmentRepositoryInterface $attachmentRepository */
    private $attachmentRepository;

    /** @var DataPersistorInterface $dataPersistor */
    private $dataPersistor;

    /** @var EmailTransport $emailTransport */
    private $emailTransport;

    /** @var Escaper $escaper */
    private $escaper;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var FormKeyValidator $formKeyValidator */
    private $formKeyValidator;

    /** @var LabelManager $labelManager */
    private $labelManager;

    /** @var OrderAdapter $orderAdapter */
    private $orderAdapter;

    /** @var RemoteAddress $remoteAddress */
    private $remoteAddress;

    /** @var Json $serializer */
    private $serializer;

    /** @var SimpleReturnInterfaceFactory $simpleReturnFactory */
    private $simpleReturnFactory;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /** @var Tokenizer $tokenizer */
    private $tokenizer;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param AttachmentRepositoryInterface $attachmentRepository
     * @param DataPersistorInterface $dataPersistor
     * @param EmailTransport $emailTransport
     * @param Escaper $escaper
     * @param EventManagerInterface $eventManager
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param LabelManager $labelManager
     * @param ConfigInterface $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param RemoteAddress $remoteAddress
     * @param Json $serializer
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param Tokenizer $tokenizer
     * @param UrlInterface $urlBuilder
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        AttachmentRepositoryInterface $attachmentRepository,
        DataPersistorInterface $dataPersistor,
        EmailTransport $emailTransport,
        Escaper $escaper,
        EventManagerInterface $eventManager,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        LabelManager $labelManager,
        ConfigInterface $moduleConfig,
        OrderAdapter $orderAdapter,
        RemoteAddress $remoteAddress,
        Json $serializer,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        Tokenizer $tokenizer,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->attachmentRepository = $attachmentRepository;
        $this->dataPersistor = $dataPersistor;
        $this->emailTransport = $emailTransport;
        $this->escaper = $escaper;
        $this->eventManager = $eventManager;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->labelManager = $labelManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderAdapter = $orderAdapter;
        $this->remoteAddress = $remoteAddress;
        $this->serializer = $serializer;
        $this->simpleReturnFactory = $simpleReturnFactory;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->tokenizer = $tokenizer;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        if (!$request->isPost()
            || !$this->formKeyValidator->validate($request)
        ) {
            return $this->getRedirectToPath(self::ROUTE_PATH);
        }

        /** @var int|string|null $rmaId */
        $rmaId = $request->getParam(self::PARAM_RMA_ID);
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        if ($rmaId !== null) {
            /** @var array|null $params */
            $params = $request->getPost('simplereturns');

            /** @var string|null $reason */
            $reason = $params['reason'] ?? null;
            $reason = !empty($reason) ? $reason : null;

            /** @var string|null $resolution */
            $resolution = $params['resolution'] ?? null;
            $resolution = !empty($resolution) ? $resolution : null;

            /** @var string|null $comments */
            $comments = $params['comments'] ?? null;
            $comments = !empty($comments) ? $comments : null;

            /** @var string|null $token */
            $token = $request->getParam(self::PARAM_TOKEN);
            $token = !empty($token) ? $token : null;

            /** @var string $remoteIp */
            $remoteIp = $this->remoteAddress->getRemoteAddress();

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

                /** @var int|string $orderId */
                $orderId = $rma->getOrderId();

                /** @var string $orderId */
                $status = $rma->getStatus();

                /** @var array $data */
                $data = [
                    'rma_id' => $rmaId,
                    'status' => $status,
                    'reason' => $reason,
                    'resolution' => $resolution,
                    'comments' => $comments,
                    'remote_ip' => $remoteIp,
                    'token' => $token,
                ];

                $this->dispatchEvent(
                    'simplereturns_rma_edit_save_before',
                    $data
                );

                $this->simpleReturnRepository->save(
                    $rma->setData($data)
                );

                $this->dispatchEvent(
                    'simplereturns_rma_edit_save_after',
                    ['rma' => $rma]
                );

                /** @var string|null $groupKey */
                $groupKey = $this->dataPersistor->get(self::DATA_GROUP_KEY);

                /* Update attachments with new RMA ID. */
                if ($groupKey !== null) {
                    /** @var array $metadata */
                    $metadata = $this->serializer->unserialize(
                        $this->dataPersistor->get($groupKey)
                            ?? $this->serializer->serialize([])
                    );

                    /** @var array $metadatum */
                    foreach ($metadata as $metadatum) {
                        /** @var int|string|null $attachmentId */
                        $attachmentId = $metadatum['attachment_id'] ?? null;
                        $attachmentId = is_numeric($attachmentId)
                            ? (int) $attachmentId : null;

                        if ($attachmentId !== null) {
                            /** @var AttachmentInterface $attachment */
                            $attachment = $this->attachmentRepository
                                ->getById($attachmentId);

                            $this->attachmentRepository->save(
                                $attachment->setRmaId($rmaId)
                            );
                        }
                    }

                    /* Clear attachment metadata, group key from session. */
                    $this->dataPersistor->clear($groupKey);
                    $this->dataPersistor->clear(self::DATA_GROUP_KEY);
                }

                /** @var OrderInterface[] $orders */
                $orders = $this->orderAdapter
                    ->getOrdersByFields(['entity_id' => $orderId]);

                /** @var OrderInterface $order */
                $order = array_shift($orders);

                /* Send Updated RMA Request email */
                $this->emailTransport->send(
                    'simplereturns/customer/rma_request_update_email_template',
                    'simplereturns/customer/rma_request_update_email_identity',
                    [
                        'orderId' => $order->getRealOrderId(),
                        'frontId' => $rma->getFrontId(),
                        'reason' => $this->labelManager->getLabel('reason', $reason),
                        'resolution' => $this->labelManager->getLabel('resolution', $resolution),
                        'status' => $this->labelManager->getLabel('status', $status),
                        'comments' => $this->escaper->escapeHtml($comments),
                    ],
                    $order->getCustomerEmail(),
                    $order->getCustomerName(),
                    (int) $order->getStoreId()
                );

                /** @var string $redirectUrl */
                $redirectUrl = $this->urlBuilder->getUrl(
                    'simplereturns/rma/view',
                    [
                        'rma_id'  => $rmaId,
                        'token'   => $token,
                        '_secure' => true,
                    ]
                );
                return $this->getRedirectToUrl($redirectUrl);
            } catch (Throwable $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->getRedirectToPath(self::ROUTE_PATH);
    }
}
