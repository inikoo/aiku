<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 15 Sep 2023 16:06:07 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Organisation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Organisation\OrganisationHumanResourcesStats
 *
 * @property-read \App\Models\Organisation\Group|null $group
 * @method static \Illuminate\Database\Eloquent\Builder|GroupHumanResourcesStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupHumanResourcesStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GroupHumanResourcesStats query()
 * @mixin \Eloquent
 */
class GroupHumanResourcesStats extends Model
{
    protected $table = 'group_human_resources_stats';

    protected $guarded = [];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
