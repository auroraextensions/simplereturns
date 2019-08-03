/**
 * builder.js
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
define([
    'jquery',
    'AuroraExtensions_SimpleReturns/js/utils/string'
], function ($, stringUtil) {
    'use strict';

    return {
        /**
         * @param {String} url
         * @param {Object} params
         * @return {String}
         */
        bindParams: function (url, params) {
            var parts;

            /** @var {Array} parts */
            parts = url.split('/').filter(Boolean);

            $.each(params, function (key, value) {
                parts.push(key, value);
            });

            return parts.join('/');
        },
        /**
         * @param {String} url
         * @param {Object} params
         * @return {String}
         */
        getUrl: function (url, params) {
            url = stringUtil.trim(url);

            return this.bindParams(url, params);
        }
    };
});
