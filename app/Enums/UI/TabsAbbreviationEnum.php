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
}
