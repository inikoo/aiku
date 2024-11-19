<?php
/*
 * author Arya Permana - Kirin
 * created on 19-11-2024-16h-41m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Ordering;

use App\Models\Traits\HasHistory;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class SalesChannel extends Model implements Auditable
{
    use HasSlug;
    use SoftDeletes;
    use HasHistory;
    use InShop;

    protected $guarded = [];

    protected array $auditInclude = [
        'name',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function stats():HasOne
    {
        return $this->hasOne(SalesChannelStats::class);
    }
}
