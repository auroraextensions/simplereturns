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
 * @package     AuroraExtensions\SimpleReturns\Controller\Adminhtml\Rma\Status
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Adminhtml\Rma\Status;

use AuroraExtensions\ModuleComponents\Component\Event\EventManagerTrait;
use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Component\System\ModuleConfigTrait;
use AuroraExtensions\SimpleReturns\Csi\System\Module\ConfigInterface;
use AuroraExtensions\SimpleReturns\Model\Display\LabelManager;
use AuroraExtensions\SimpleReturns\Model\Email\Transport\Customer as EmailTransport;
use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;
use Throwable;

use function __;
use function is_numeric;
use function trim;

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
     */
    use EventManagerTrait, ModuleConfigTrait;

    private const PARAM_RMA_ID = 'rma_id';
    private const PARAM_TOKEN = 'token';

    /** @var EmailTransport $emailTransport */
    private $emailTransport;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var FormKeyValidator $formKeyValidator */
    private $formKeyValidator;

    /** @var LabelManager $labelManager */
    private $labelManager;

    /** @var OrderRepositoryInterface $orderRepository */
    private $orderRepository;

    /** @var ResultJsonFactory $resultJsonFactory */
    private $resultJsonFactory;

    /** @var Json $serializer */
    private $serializer;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /**
     * @param Context $context
     * @param EmailTransport $emailTransport
     * @param EventManagerInterface $eventManager
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param LabelManager $labelManager
     * @param ConfigInterface $moduleConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param ResultJsonFactory $resultJsonFactory
     * @param Json $serializer
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        EmailTransport $emailTransport,
        EventManagerInterface $eventManager,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        LabelManager $labelManager,
        ConfigInterface $moduleConfig,
        OrderRepositoryInterface $orderRepository,
        ResultJsonFactory $resultJsonFactory,
        Json $serializer,
        SimpleReturnRepositoryInterface $simpleReturnRepository
    ) {
        parent::__construct($context);
        $this->emailTransport = $emailTransport;
        $this->eventManager = $eventManager;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->labelManager = $labelManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderRepository = $orderRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer;
        $this->simpleReturnRepository = $simpleReturnRepository;
    }

    /**
     * @return ResultJson
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var array $response */
        $response = [];

        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        /** @var ResultJson $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        if (!$request->isPost()) {
            $resultJson->setData([
                'error' => true,
                'message' => __('Invalid method: Must be POST request.'),
            ]);
            return $resultJson;
        }

        if (!$this->formKeyValidator->validate($request)) {
            $resultJson->setData([
                'error' => true,
                'message' => __('Invalid form key.'),
            ]);
            return $resultJson;
        }

        /** @var int|string|null $rmaId */
        $rmaId = $request->getParam(self::PARAM_RMA_ID);
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        /** @var string|null $token */
        $token = $request->getParam(self::PARAM_TOKEN);
        $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

        /** @var string $content */
        $content = $request->getContent()
            ?? $this->serializer->serialize([]);

        /** @var array $data */
        $data = (array) $this->serializer->unserialize($content);

        /** @var string|null $status */
        $status = $data['status'] ?? null;
        $status = $status !== null ? trim($status) : null;

        if ($rmaId !== null && $token !== null
            && $status !== null
        ) {
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

                /** @var string $reason */
                $reason = $rma->getReason();

                /** @var string $resolution */
                $resolution = $rma->getResolution();

                $this->dispatchEvent(
                    'simplereturns_adminhtml_rma_status_edit_save_before',
                    [
                        'rma' => $rma,
                        'status' => $status,
                    ]
                );

                $this->simpleReturnRepository->save(
                    $rma->setStatus($status)
                );

                $this->dispatchEvent(
                    'simplereturns_adminhtml_rma_status_edit_save_after',
                    ['rma' => $rma]
                );

                /** @var OrderInterface $order */
                $order = $this->orderRepository->get($orderId);

                /* Send RMA request status change email. */
                $this->emailTransport->send(
                    'simplereturns/customer/rma_request_status_change_email_template',
                    'simplereturns/customer/rma_request_status_change_email_identity',
                    [
                        'orderId' => $order->getRealOrderId(),
                        'uuid' => $rma->getUuid(),
                        'reason' => $this->labelManager->getLabel('reason', $reason),
                        'resolution' => $this->labelManager->getLabel('resolution', $resolution),
                        'status' => $this->labelManager->getLabel('status', $status),
                    ],
                    $order->getCustomerEmail(),
                    $order->getCustomerName(),
                    (int) $order->getStoreId()
                );

                $resultJson->setData([
                    'success' => true,
                    'message' => __('Successfully updated RMA status.'),
                ]);
                return $resultJson;
            } catch (Throwable $e) {
                $response = [
                    'error' => true,
                    'message' => $e->getMessage(),
                ];
            }
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}
