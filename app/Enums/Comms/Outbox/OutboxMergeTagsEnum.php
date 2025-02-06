<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Dec 2024 22:42:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\Outbox;

use App\Enums\EnumHelperTrait;

enum OutboxMergeTagsEnum: string
{
    use EnumHelperTrait;

    case USERNAME = 'Username';
    case CUSTOMER_NAME = 'Customer Name';
    case INVOICE_URL = 'Invoice_Url';
    case RESET_PASSWORD_URL = 'Reset_Password_URL';
    case UNSUBSCRIBE = 'Unsubscribe';

    public static function tags(): array
    {
        return [
            [
                'name' => __('Username'),
                'value' => '[Username]'
            ],
            [
                'name' => __('Customer Name'),
                'value' => '[Customer Name]'
            ],
            [
                'name' => __('Invoice URL'),
                'value' => '[Invoice_Url]'
            ],
            [
                'name' => __('Reset Password URL'),
                'value' => '[Reset_Password_URL]'
            ],
            [
                'name' => __('Unsubscribe'),
                'value' => '[Unsubscribe]'
            ],
        ];
    }
}
