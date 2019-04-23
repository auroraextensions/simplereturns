/**
 * print.js
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/returns/LICENSE.txt
 *
 * @package       AuroraExtensions_Returns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
define([
    'jquery',
    'jquery/ui',
    'jquery.print'
], function ($) {
    'use strict';

    $.widget('mage.printLabel', {
        options: {
            labelContainer: ".content-label"
        },
        _create: function () {
            this.element.on('click', $.proxy(this._print, this));
        },
        _print: function (clickEvent) {
            $(this.options.labelContainer).print();
        }
    });

    return $.mage.printLabel;
});