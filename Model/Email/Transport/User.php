<?php
/**
 * User.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\Email\Transport
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\Email\Transport;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\App\ConfigInterface;
use Magento\Email\Model\BackendTemplate;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;

class User
{
    /** @var ConfigInterface $backendConfig */
    private $backendConfig;

    /** @var TransportBuilder $transportBuilder */
    private $transportBuilder;

    /**
     * @param ConfigInterface $backendConfig
     * @param TransportBuilder $transportBuilder
     * @return void
     */
    public function __construct(
        ConfigInterface $backendConfig,
        TransportBuilder $transportBuilder
    ) {
        $this->backendConfig = $backendConfig;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * @param string $template Template configuration ID.
     * @param string $identity Email sender identity XML path.
     * @param array $params
     * @param string|null $email
     * @param string|null $name
     * @return $this
     */
    public function send(
        string $template,
        string $identity,
        array $params = [],
        string $email = null,
        string $name = null
    ) {
        /** @var array $options */
        $options = [
            'area'  => FrontNameResolver::AREA_CODE,
            'store' => Store::DEFAULT_STORE_ID,
        ];

        /** @var string $templateId */
        $templateId = $this->backendConfig->getValue($template);

        /** @var string $sender */
        $sender = $this->backendConfig->getValue($identity);

        /** @var Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateModel(BackendTemplate::class)
            ->setTemplateVars($params)
            ->setTemplateOptions($options)
            ->setFrom($sender)
            ->addTo($email, $name)
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }
}
