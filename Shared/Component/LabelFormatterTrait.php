<?php
/**
 * LabelFormatterTrait.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Shared\Component;

/**
 * @todo: Decouple from ModuleConfigTrait.
 */
trait LabelFormatterTrait
{
    /**
     * Get frontend label for field type by key.
     *
     * @param string $type
     * @param string $key
     * @param string
     */
    public function getFrontLabel(string $type, string $key): string
    {
        /** @var array $labels */
        $labels = $this->getConfig()
            ->getSettings()
            ->getData($type);

        return $labels[$key] ?? $key;
    }
}
