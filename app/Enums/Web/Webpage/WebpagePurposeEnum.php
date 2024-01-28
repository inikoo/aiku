<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 07 Jun 2023 01:32:24 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Web\Webpage;

use App\Enums\EnumHelperTrait;

enum WebpagePurposeEnum: string
{
    use EnumHelperTrait;

    case STOREFRONT = 'storefront';

    case PRODUCT_OVERVIEW = 'product-overview';
    case PRODUCT_LIST     = 'product-list';

    case CATEGORY_PREVIEW = 'category-preview';

    case SHOPPING_CART = 'shopping-cart';




    case INFO = 'info';

    case PRIVACY        = 'privacy';
    case COOKIES_POLICY = 'cookies-policy';

    case TERMS_AND_CONDITIONS = 'terms-and-conditions';

    case APPOINTMENT = 'appointment';

    case CONTACT = 'contact';

    case LOGIN    = 'login';
    case REGISTER = 'register';

    case BLOG    = 'blog';
    case ARTICLE = 'article';

    case CONTENT = 'content';

    case OTHER_SMALL_PRINT = 'other-small-print';

    case SHOP = 'shop';


    public static function labels(): array
    {
        return [
            'storefront'  => __('storefront'),
            'appointment' => __('appointment'),
            'login'       => __('login'),
            'register'    => __('register'),
            'blog'        => __('blog'),
            'article'     => __('article'),
            'content'     => __('content'),

        ];
    }
}
