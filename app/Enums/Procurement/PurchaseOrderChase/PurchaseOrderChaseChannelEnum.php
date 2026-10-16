<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\PurchaseOrderChase;

use App\Enums\EnumHelperTrait;

enum PurchaseOrderChaseChannelEnum: string
{
    use EnumHelperTrait;

    case WECHAT = 'wechat';
    case WHATSAPP = 'whatsapp';
    case PHONE = 'phone';
    case EMAIL = 'email';
    case VISIT = 'visit';
    case OTHER = 'other';

    public static function labels(): array
    {
        return [
            'wechat'   => __('WeChat'),
            'whatsapp' => __('WhatsApp'),
            'phone'    => __('Phone'),
            'email'    => __('Email'),
            'visit'    => __('Visit'),
            'other'    => __('Other'),
        ];
    }
}
