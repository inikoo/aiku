<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 20 Oct 2022 19:12:42 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


/**
 * App\Models\Marketing\FamilyStats
 *
 * @property int $id
 * @property int $family_id
 * @property int $number_products
 * @property int $number_products_state_in_process
 * @property int $number_products_state_active
 * @property int $number_products_state_discontinuing
 * @property int $number_products_state_discontinued
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Marketing\Family $family
 * @method static Builder|FamilyStats newModelQuery()
 * @method static Builder|FamilyStats newQuery()
 * @method static Builder|FamilyStats query()
 * @mixin \Eloquent
 */
class FamilyStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'family_stats';

    protected $guarded = [];


    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }
}
