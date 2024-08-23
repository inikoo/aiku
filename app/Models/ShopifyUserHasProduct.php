<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Aug 2024 17:27:59 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 *
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasProduct query()
 * @mixin \Eloquent
 */
class ShopifyUserHasProduct extends Pivot
{
    protected $table = 'shopify_user_has_products';
}
