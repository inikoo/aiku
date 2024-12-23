<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 21 Dec 2024 14:19:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property-read \App\Models\Catalogue\Collection|null $collection
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionsOrderingStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionsOrderingStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CollectionsOrderingStats query()
 * @mixin \Eloquent
 */
class CollectionsOrderingStats extends Model
{
    protected $table = 'collection_ordering_stats';

    protected $guarded = [];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
