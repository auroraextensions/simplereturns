<?php
/**
 * User.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package        AuroraExtensions_SimpleReturns
 * @copyright      Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license        Aurora Extensions EULA
 */
namespace AuroraExtensions\SimpleReturns\Model\Email\Transport;

use AuroraExtensions\SimpleReturns\{
    Model\Email\AbstractTransport,
    Shared\ModuleComponentInterface
};
use Magento\Backend\{
    App\Area\FrontNameResolver,
    App\ConfigInterface
};
use Magento\Email\Model\BackendTemplate;
use Magento\Framework\{
    App\Config\ScopeConfigInterface,
    Mail\Template\TransportBuilder
};
use Magento\Store\Model\Store;

class User extends AbstractTransport implements ModuleComponentInterface
{
    /**
     * Send email notification to administrator.
     *
     * @param string $templateConfigId
     * @param array $templateVars
     * @param string|null $recipientEmail
     * @param string|null $recipientName
     * @return $this
     */
    public function sendEmailNotification(
        string $templateConfigId,
        array $templateVars,
        string $recipientEmail = null,
        string $recipientName = null
    ) {
        /** @var array $options */
        $options = [
            'area'  => FrontNameResolver::AREA_CODE,
            'store' => Store::DEFAULT_STORE_ID,
        ];

        /** @var string $sender */
        $sender = $this->getConfig()->getValue(
            self::XML_PATH_ADMIN_LOGIN_REQUEST_EMAIL_IDENTITY
        );

        $this->getTransportBuilder()
            ->setTemplateIdentifier($this->getConfig()->getValue($templateConfigId))
            ->setTemplateModel(BackendTemplate::class)
            ->setTemplateVars($templateVars)
            ->setTemplateOptions($options)
            ->setFrom($sender)
            ->addTo($recipientEmail, $recipientName)
            ->getTransport()
            ->sendMessage();

        return $this;
    }
}
