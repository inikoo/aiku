<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Jan 2025 13:12:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Enums\Accounting\Invoice;

use App\Enums\EnumHelperTrait;

enum InvoicePayStatusEnum: string
{
    use EnumHelperTrait;

    case UNPAID = 'unpaid';
    case PAID = 'paid';

    public static function labels(): array
    {
        return [
            'unpaid' => __('Unpaid'),
            'paid'   => __('Paid'),
        ];
    }

    public static function typeIcon(): array
    {
        return [
            'unpaid' => [
                'tooltip' => __('Unpaid'),
                'icon'    => 'fal fa-file-invoice-dollar',
                'class'   => '',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ],
            'paid'   => [
                'tooltip' => __('Paid'),
                'icon'    => 'fal fa-hand-holding-usd',
                'class'   => '',  // Color for normal icon (Aiku)
                'color'   => 'lime',  // Color for box (Retina)
                'app'     => [
                    'name' => 'seedling',
                    'type' => 'font-awesome-5'
                ]
            ]
        ];
    }
}
