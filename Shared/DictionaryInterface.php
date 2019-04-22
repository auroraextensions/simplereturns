<?php
/**
 * DictionaryInterface.php
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
namespace AuroraExtensions\Returns\Shared;

use AuroraExtensions\Returns\Controller\{
    Label\Index as IndexController,
    Label\Orders as OrdersController,
    Label\OrdersPost as OrdersPostController
};

interface DictionaryInterface
{
    /** @constant string ATTRIBUTE_CODE_RETURN_LABEL_ALLOWED */
    const ATTRIBUTE_CODE_RETURN_LABEL_ALLOWED = 'return_label_allowed';

    /** @constant string ATTRIBUTE_LABEL_RETURN_LABEL_ALLOWED */
    const ATTRIBUTE_LABEL_RETURN_LABEL_ALLOWED = 'Return Label Allowed';

    /** @constant string BLOCK_RETURNS_LABEL_INDEX */
    const BLOCK_RETURNS_LABEL_INDEX = 'returns_label_index';

    /** @constant string BLOCK_RETURNS_LABEL_ORDERS */
    const BLOCK_RETURNS_LABEL_ORDERS = 'returns_label_orders';

    /** @constant string COLUMN_HEADERS_KEY */
    const COLUMN_HEADERS_KEY = 'column_headers';

    /** @constant string DATA_PERSISTOR_KEY */
    const DATA_PERSISTOR_KEY = 'auroraextensions_returns_data';

    /** @constant string DEFAULT_FRONT_NAME */
    const DEFAULT_FRONT_NAME = 'returns';

    /** @constant string DEFAULT_SCRIPT_NAME */
    const DEFAULT_SCRIPT_NAME = 'index.php';

    /** @constant string ERROR_INVALID_TRAIT_CONTEXT */
    const ERROR_INVALID_TRAIT_CONTEXT = '%1 can only be used when extending or implementing %2';

    /** @constant string ERROR_MISSING_URL_PARAMS */
    const ERROR_MISSING_URL_PARAMS = 'Please provide an email address or order ID and billing/shipping zip code.';

    /** @constant string FULLACTION_DELIMITER */
    const FULLACTION_DELIMITER = '_';

    /** @constant string FULLACTION_RETURNS_LABEL_INDEX */
    const FULLACTION_RETURNS_LABEL_INDEX = 'returns_label_index';

    /** @constant string FULLACTION_RETURNS_LABEL_ORDERS */
    const FULLACTION_RETURNS_LABEL_ORDERS = 'returns_label_orders';

    /** @constant string FULLACTION_RETURNS_LABEL_ORDERSPOST */
    const FULLACTION_RETURNS_LABEL_ORDERSPOST = 'returns_label_ordersPost';

    /** @constant string PARAM_EMAIL */
    const PARAM_EMAIL = 'email';

    /** @constant string PARAM_ORDER_ID */
    const PARAM_ORDER_ID = 'order_id';

    /** @constant string PARAM_PROTECT_CODE */
    const PARAM_PROTECT_CODE = 'code';

    /** @constant string PARAM_ZIP_CODE */
    const PARAM_ZIP_CODE = 'zip_code';

    /** @constant string PATH_INDEX_DELIMITER */
    const PATH_INDEX_DELIMITER = '/';

    /** @constant string PREFIX_DATAURI */
    const PREFIX_DATAURI = 'data:image/jpeg;base64,';

    /** @constant string ROUTE_RETURNS_LABEL_INDEX */
    const ROUTE_RETURNS_LABEL_INDEX = 'returns/label/index';

    /** @constant string ROUTE_RETURNS_LABEL_ORDERS */
    const ROUTE_RETURNS_LABEL_ORDERS = 'returns/label/orders';

    /** @constant string ROUTE_RETURNS_LABEL_ORDERSPOST */
    const ROUTE_RETURNS_LABEL_ORDERSPOST = 'returns/label/ordersPost';

    /** @constant string XML_LAYOUT_HANDLE_NOROUTE */
    const XML_LAYOUT_HANDLE_NOROUTE = 'returns_noroute';

    /** @constant array DICT_ACTION_CONTROLLER_DISPATCH */
    const DICT_ACTION_CONTROLLER_DISPATCH = [
        self::FULLACTION_RETURNS_LABEL_INDEX      => IndexController::class,
        self::FULLACTION_RETURNS_LABEL_ORDERS     => OrdersController::class,
        self::FULLACTION_RETURNS_LABEL_ORDERSPOST => OrdersPostController::class,
    ];
}
