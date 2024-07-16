<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Jul 2024 13:36:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $insurance_id
 * @property int $number_historic_assets
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Catalogue\Insurance $insurance
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InsuranceStats query()
 * @mixin \Eloquent
 */
class InsuranceStats extends Model
{
    protected $table = 'insurance_stats';

    protected $guarded = [];

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(Insurance::class);
    }
}
