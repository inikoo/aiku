<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 14:55:08 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ShippingZone extends Model
{
    use SoftDeletes;
    use UsesTenantConnection;
    use HasSlug;


    protected $casts = [
        'territories' => 'array',
        'price' => 'array',
        'status' => 'boolean',
    ];

    protected $attributes = [
        'territories' => '{}',
        'price' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(6);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
