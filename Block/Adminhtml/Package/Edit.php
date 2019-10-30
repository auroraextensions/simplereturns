<?php
/**
 * Edit.php
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

namespace AuroraExtensions\SimpleReturns\Block\Adminhtml\Package;

use AuroraExtensions\SimpleReturns\{
    Model\Security\Token as Tokenizer,
    Shared\ModuleComponentInterface
};
use Magento\Backend\{
    Block\Widget\Context,
    Block\Widget\Container
};

class Edit extends Container implements ModuleComponentInterface
{
    /** @property string $_blockGroup */
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
        $pkgId = $pkgId !== null && is_numeric($pkgId)
            ? (int) $pkgId
            : null;

        if ($pkgId !== null) {
            /** @var string|null $token */
            $token = $this->getRequest()->getParam(self::PARAM_TOKEN);
            $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

            if ($token !== null) {
                /** @var string $targetUrl */
                $targetUrl = $this->getUrl(
                    'simplereturns/package/edit',
                    [
                        'pkg_id' => $pkgId,
                        'token' => $token,
                    ]
                );

                return "(function(){window.location='{$targetUrl}';})();";
            }
        }

        return null;
    }
}
