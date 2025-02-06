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
            OutboxMergeTagsEnum::USERNAME->value => [
                'label' => __('Username'),
                'value' => '[Username]'
            ],
            OutboxMergeTagsEnum::CUSTOMER_NAME->value => [
                'label' => __('Customer Name'),
                'value' => '[Customer Name]'
            ],
            OutboxMergeTagsEnum::INVOICE_URL->value => [
                'label' => __('Invoice URL'),
                'value' => '[Invoice_Url]'
            ],
            OutboxMergeTagsEnum::RESET_PASSWORD_URL->value => [
                'label' => __('Reset Password URL'),
                'value' => '[Reset_Password_URL]'
            ],
            OutboxMergeTagsEnum::UNSUBSCRIBE->value => [
                'label' => __('Unsubscribe'),
                'value' => '[Unsubscribe]'
            ],
        ];
    }
}
