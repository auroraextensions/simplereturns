<?php
/**
 * Generate.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Block\Adminhtml\Label
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Block\Adminhtml\Label;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Api\Data\PackageInterface,
    Api\PackageRepositoryInterface,
    Model\Security\Token as Tokenizer,
    Shared\ModuleComponentInterface
};
use Magento\Backend\{
    Block\Widget\Context,
    Block\Widget\Container
};
use Magento\Framework\{
    Exception\LocalizedException,
    Exception\NoSuchEntityException
};

use function __;
use function is_numeric;

class Generate extends Container implements ModuleComponentInterface
{
    /** @var string $_blockGroup */
    protected $_blockGroup = 'AuroraExtensions_SimpleReturns';

    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var PackageRepositoryInterface $packageRepository */
    protected $packageRepository;

    /**
     * @param Context $context
     * @param ExceptionFactory $exceptionFactory
     * @param PackageRepositoryInterface $packageRepository
     * @param array $data
     * @return void
     */
    public function __construct(
        Context $context,
        ExceptionFactory $exceptionFactory,
        PackageRepositoryInterface $packageRepository,
        array $data = []
    ) {
        $this->exceptionFactory = $exceptionFactory;
        $this->packageRepository = $packageRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_objectId = 'simplereturns_label_generate';
        $this->_controller = 'adminhtml_label';
        $this->setId('simplereturns_label_generate');

        if (!$this->hasLabel()) {
            $this->addButton(
                'simplereturns_label_generate',
                [
                    'class' => 'generate primary',
                    'id' => 'simplereturns-label-generate',
                    'label' => __('Generate Shipping Label'),
                    'onclick' => $this->getOnClickJs() ?? '',
                ]
            );
        } else {
            $this->addButton(
                'simplereturns_label_print',
                [
                    'class' => 'print primary',
                    'id' => 'simplereturns-label-print',
                    'data_attribute' => [
                        'mage-init' => [
                            'simpleReturnsLabelPrint' => [],
                        ],
                    ],
                    'label' => __('Print Shipping Label'),
                ]
            );
        }
    }

    /**
     * @return bool
     */
    protected function hasLabel(): bool
    {
        /** @var int|string|null $pkgId */
        $pkgId = $this->getRequest()->getParam(self::PARAM_PKG_ID);
        $pkgId = is_numeric($pkgId) ? (int) $pkgId : null;

        /** @var string|null $token */
        $token = $this->getRequest()->getParam(self::PARAM_TOKEN);
        $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

        if ($pkgId !== null && $token !== null) {
            try {
                /** @var PackageInterface $package */
                $package = $this->packageRepository->getById($pkgId);

                if (!Tokenizer::isEqual($token, $package->getToken())) {
                    /** @var LocalizedException $exception */
                    $exception = $this->exceptionFactory->create(
                        LocalizedException::class
                    );
                    throw $exception;
                }

                if ($package->getLabelId() !== null) {
                    return true;
                }
            } catch (NoSuchEntityException | LocalizedException $e) {
                /* No action required. */
            }
        }

        return false;
    }

    /**
     * @return string|null
     */
    protected function getOnClickJs(): ?string
    {
        /** @var int|string|null $pkgId */
        $pkgId = $this->getRequest()->getParam(self::PARAM_PKG_ID);
        $pkgId = is_numeric($pkgId) ? (int) $pkgId : null;

        /** @var string|null $token */
        $token = $this->getRequest()->getParam(self::PARAM_TOKEN);
        $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

        if ($pkgId !== null && $token !== null) {
            /** @var string $targetUrl */
            $targetUrl = $this->getUrl(
                'simplereturns/label/generate',
                [
                    'pkg_id' => $pkgId,
                    'token' => $token,
                ]
            );
            return "(function(){window.location.href='{$targetUrl}';})();";
        }

        return null;
    }
}
