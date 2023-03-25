<?php
/**
 * Edit.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Block\Adminhtml\Package
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Block\Adminhtml\Package;

use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;

use function __;
use function is_numeric;

class Edit extends Container
{
    private const PARAM_PKG_ID = 'pkg_id';
    private const PARAM_TOKEN = 'token';

    /** @var string $_blockGroup */
    protected $_blockGroup = 'AuroraExtensions_SimpleReturns';

    /**
     * @param Context $context
     * @param array $data
     * @return void
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_objectId = 'simplereturns_package_edit';
        $this->_controller = 'adminhtml_package';
        $this->setId('simplereturns_package_edit');

        $this->addButton(
            'simplereturns_package_edit',
            [
                'class' => 'edit secondary',
                'id' => 'simplereturns-package-edit',
                'label' => __('Edit'),
                'onclick' => $this->getOnClickJs() ?? '',
            ]
        );
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
                'simplereturns/package/edit',
                [
                    self::PARAM_PKG_ID => $pkgId,
                    self::PARAM_TOKEN => $token,
                ]
            );
            return "(function(){window.location.href='{$targetUrl}';})();";
        }

        return null;
    }
}
