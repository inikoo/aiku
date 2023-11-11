<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:22 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Organisation;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Organisation\OrganisationFulfilmentStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_customers_with_stocks
 * @property int $number_customers_with_stored_items
 * @property int $number_customers_with_active_stocks
 * @property int $number_customers_with_assets
 * @property int $number_stored_items
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Organisation\Organisation $organisation
 * @method static Builder|OrganisationFulfilmentStats newModelQuery()
 * @method static Builder|OrganisationFulfilmentStats newQuery()
 * @method static Builder|OrganisationFulfilmentStats query()
 * @mixin Eloquent
 */
class OrganisationFulfilmentStats extends Model
{
    protected $table = 'organisation_fulfilment_stats';

    protected $guarded = [];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
