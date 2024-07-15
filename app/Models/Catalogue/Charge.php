<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Jul 2024 13:36:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Charge extends Model implements Auditable
{
    use SoftDeletes;
    use HasUniversalSearch;
    use InAssetModel;
    use HasHistory;
    use HasFactory;
    use HasSlug;

    protected $guarded = [];

    protected $casts = [
        'price'    => 'decimal:2',
        'state'    => ChargeStateEnum::class,
        'status'   => 'boolean',
        'data'     => 'array',
        'settings' => 'array',
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    public function generateTags(): array
    {
        return [
            'catalogue',
        ];
    }

    protected array $auditInclude = [
        'code',
        'name',
        'description',
        'price',
        'currency_id',
        'units',
        'unit',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->shop->slug.'-'.$this->code;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(ChargeStats::class);
    }

}
