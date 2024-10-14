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
    case CATALOGUE = 'catalogue';
    case CONTENT = 'content';
    case INFO = 'info';
    case OPERATIONS = 'operations';
    case BLOG = 'blog';

    public static function labels(): array
    {
        return [
            'storefront' => __('storefront'),
            'catalogue'  => __('catalogue'),
            'content'    => __('content'),
            'info'       => __('info'),
            'blog'       => __('blog'),
            'operations' => __('operations'),
        ];
    }

    public function label(): string
    {
        return Arr::get($this->labels(), $this->value);
    }

}
