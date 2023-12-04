<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:32:22 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\SysAdmin\OrganisationProductionStats
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $number_products
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @method static Builder|OrganisationProductionStats newModelQuery()
 * @method static Builder|OrganisationProductionStats newQuery()
 * @method static Builder|OrganisationProductionStats query()
 * @mixin Eloquent
 */
class OrganisationProductionStats extends Model
{
    protected $table = 'organisation_production_stats';

    protected $guarded = [];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
