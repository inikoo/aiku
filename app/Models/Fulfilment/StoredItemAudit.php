<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 17:02:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Enums\Fulfilment\StoredItemAudit\StoredItemAuditStateEnum;
use App\Models\Inventory\Warehouse;
use App\Models\Traits\HasRetinaSearch;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InFulfilmentCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $slug
 * @property int $fulfilment_customer_id
 * @property int $fulfilment_id
 * @property int|null $warehouse_id
 * @property string $reference
 * @property StoredItemAuditStateEnum $state
 * @property \Illuminate\Support\Carbon|null $in_process_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $public_notes
 * @property string|null $internal_notes
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Fulfilment\StoredItemAuditDelta> $deltas
 * @property-read \App\Models\Fulfilment\Fulfilment $fulfilment
 * @property-read \App\Models\Fulfilment\FulfilmentCustomer $fulfilmentCustomer
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Helpers\RetinaSearch|null $retinaSearch
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
 * @property-read Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemAudit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemAudit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoredItemAudit query()
 * @mixin \Eloquent
 */
class StoredItemAudit extends Model
{
    use HasSlug;
    use HasUniversalSearch;
    use HasRetinaSearch;
    use InFulfilmentCustomer;

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $casts = [
        'state'         => StoredItemAuditStateEnum::class,
        'in_process_at' => 'datetime',
        'completed_at'  => 'datetime',
        'data'          => 'array'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('reference')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(64);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function deltas(): HasMany
    {
        return $this->hasMany(StoredItemAuditDelta::class);
    }


}