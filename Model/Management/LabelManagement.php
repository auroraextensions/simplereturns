<?php
/**
 * LabelManagement.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\Management
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\Management;

use AuroraExtensions\SimpleReturns\Api\Data\LabelInterface;
use AuroraExtensions\SimpleReturns\Api\LabelManagementInterface;

use function base64_encode;

class LabelManagement implements LabelManagementInterface
{
    private const PREFIX_DATAURI = 'data:image/jpeg;base64,';

    /**
     * Get image as data URI.
     *
     * @param LabelInterface $label
     * @return string
     */
    public function getImageDataUri(LabelInterface $label): string
    {
        return self::PREFIX_DATAURI . base64_encode($label->getImage());
    }
}
