<?php
/**
 * FormatterTrait.php
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
namespace AuroraExtensions\SimpleReturns\Shared\Component;

trait FormatterTrait
{
    /**
     * @return string
     */
    public function getFrontId(): string
    {
        return sprintf(
            self::FORMAT_FRONT_ID,
            $this->getId()
        );
    }
}
