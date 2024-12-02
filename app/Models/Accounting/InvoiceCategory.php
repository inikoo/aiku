<?php

/*
 * author Arya Permana - Kirin
 * created on 28-10-2024-10h-52m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Accounting;

use App\Enums\Accounting\Invoice\InvoiceCategoryStateEnum;
use App\Models\Traits\HasHistory;
use App\Models\Traits\InGroup;
use App\Stubs\Migrations\HasSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property string $slug
 * @property string $name
 * @property InvoiceCategoryStateEnum $state
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property string|null $source_id
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\Accounting\InvoiceCategoryOrderingIntervals|null $orderingIntervals
 * @property-read \App\Models\Accounting\InvoiceCategorySalesIntervals|null $salesIntervals
 * @property-read \App\Models\Accounting\InvoiceCategoryStats|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceCategory query()
 * @mixin \Eloquent
 */
class InvoiceCategory extends Model implements Auditable
{
    use HasSlug;
    use HasHistory;
    use HasSoftDeletes;
    use InGroup;

    protected $casts = [
        'state'            => InvoiceCategoryStateEnum::class,
    ];

    protected $guarded = [];

    public function generateTags(): array
    {
        return ['accounting'];
    }

    protected array $auditInclude = [
        'name',
        'state',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function stats(): HasOne
    {
        return $this->hasOne(InvoiceCategoryStats::class);
    }

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(InvoiceCategorySalesIntervals::class);
    }

    public function orderingIntervals(): HasOne
    {
        return $this->hasOne(InvoiceCategoryOrderingIntervals::class);
    }
}
