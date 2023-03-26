/**
 * parser.js
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
define([
    'jquery',
    'AuroraExtensions_SimpleReturns/js/utils/string'
], function ($, stringUtil) {
    'use strict';

    return {
        /**
         * @param {String} key
         * @param {String} url
         * @return {Boolean}
         */
        hasParam: function (key, url) {
            url = url || document.location.pathname;

            return !!(url.split(key).filter(Boolean).length - 1);
        },
        /**
         * @param {String} key
         * @param {String} url
         * @return {String}
         */
        getParamValue: function (key, url) {
            var index, parts, value;

            url = url || document.location.pathname;
            url = stringUtil.trim(url);

            if (!this.hasParam(key, url)) {
                return null;
            }

            /** @var {Array} parts */
            parts = url.split('/').filter(Boolean);

            /** @var {Number} index */
            index = parts.indexOf(key);

            if (index !== -1) {
                /** @var {String} value */
                value = parts.slice(index)[1];

                return value ? value : null;
            }

            return null;
        }
    };
});
