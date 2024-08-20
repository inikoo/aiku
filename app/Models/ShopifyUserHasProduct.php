<?php

namespace App\Models;

use Google\Service\AnalyticsData\Pivot;

/**
 *
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopifyUserHasProduct query()
 * @mixin \Eloquent
 */
class ShopifyUserHasProduct extends Pivot
{
}
