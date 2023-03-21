<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 23:55:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

enum TabsAbbreviationEnum: string
{
    case STATS           = 'sts';
    case WAREHOUSE_AREAS = 'wa';
    case LOCATIONS       = 'loc';

    case PAYMENT_ACCOUNTS = 'pa';
    case PAYMENTS         = 'p';

    case PORTFOLIO         = 'po';
    case PRODUCTS          = 'prod';
    case ORDERS            = 'o';
    case SALES             = 'sales';
    case INSIGHTS          = 'i';
    case DISCOUNTS         = 'disc';
    case CREDITS           = 'cred';
    case ATTACHMENTS       = 'attach';
    case DISPATCHED_EMAILS = 'de';

    case DATA      = 'data';
    case CHANGELOG = 'hist';
    case CUSTOMERS = 'cus';

    case PURCHASES_SALES    = 'ps';
    case SUPPLIER_PRODUCTS  = 'sp';

    case ISSUES             = 'iss';

    case PURCHASE_ORDERS   = 'puord';

    case DELIVERIES         = 'd';

    case IMAGES             = 'img';

    case SYSTEM_USERS       = 'syuser';

    case ITEMS                     = 'itms';

    case CUSTOMER_NOTES_HISTORY     = 'cnh';
    case SENT_EMAILS                = 'se';

    case INVOICES                   = 'inv';
    case DELIVERY_NOTES             = 'dn';

    case SKOS_ORDERED           = 'skoord';

    case UNITS                  = 'u';
    case TARIFF_CODES_ORIGIN    = 'tco';
    case PROPERTIES_OPERATIONS  = 'propt';
    case AGENTS_SUPPLIERS       = 'as';
    case AGENTS_PARTS           = 'ap';

    case STOCK_HISTORY      = 'sh';

    case PARTS              = 'parts';
    case DISCONTINUED_PARTS = 'dp';
    case PARTS_LOCATIONS    = 'pl';
    case PRODUCT_FAMILIES   = 'pf';

    case STOCK_MOVEMENTS    = 'sm';
}
