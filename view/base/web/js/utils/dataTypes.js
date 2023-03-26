/**
 * dataTypes.js
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
define([], function () {
    'use strict';

    return {
        /** @property {String} marker */
        marker: ';base64,',
        /**
         * @param {String} dataUri
         * @return {Array}
         */
        dataUriToBinary: function (dataUri) {
            var binary, buffer,
                index, length,
                start, value;

            /** @var {Number} start */
            start = dataUri.indexOf(this.marker) + this.marker.length;

            /** @var {String} value */
            value = dataUri.substring(start);

            /** @var {ArrayBuffer} binary */
            binary = window.atob(value);

            /** @var {Number} length */
            length = binary.length;

            /** @var {Uint8Array} buffer */
            buffer = new Uint8Array(new ArrayBuffer(length));

            for (index = 0; index < length; index += 1) {
                buffer[index] = binary.charCodeAt(index);
            }

            return buffer;
        }
    };
});
