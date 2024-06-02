<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 23:17:02 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $subscription_id
 * @property int $number_historic_assets
 * @property int $number_subscriptions_state_in_process
 * @property int $number_subscriptions_state_active
 * @property int $number_subscriptions_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\Subscription|null $asset
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubscriptionStats query()
 * @mixin \Eloquent
 */
class SubscriptionStats extends Model
{
    protected $table = 'subscription_stats';

    protected $guarded = [];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
