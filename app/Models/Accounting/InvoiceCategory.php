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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class InvoiceCategory extends Model implements Auditable
{
    use HasSlug;
    use HasHistory;

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
}
