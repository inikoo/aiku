<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Jun 2023 01:35:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Web\Webpage;

use App\Enums\EnumHelperTrait;
use Illuminate\Support\Arr;

enum WebpageTypeEnum: string
{
    use EnumHelperTrait;


    case STOREFRONT = 'storefront';
    case SHOP       = 'shop';
    case CHECKOUT   = 'checkout';
    case CONTENT    = 'content';

    case SMALL_PRINT = 'small-print';
    case ENGAGEMENT  = 'engagement';
    case AUTH        = 'auth';

    case BLOG = 'blog';

    public static function labels(): array
    {
        return [
            'storefront'  => __('storefront'),
            'shop'        => __('shop'),
            'content'     => __('content'),
            'info'        => __('info'),
            'small-print' => __('small print'),
            'engagement'  => __('engagement'),
            'auth'        => __('authorisation'),
            'blog'        => __('blog'),
            'checkout'    => __('checkout'),
        ];
    }

    public function label(): string
    {
        return Arr::get($this->labels(), $this->value);
    }

}
