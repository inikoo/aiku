<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Apr 2024 19:31:40 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Market;

use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\SlugOptions;

trait IsOuterable {


    public function getRouteKeyName(): string
    {
        return 'slug';
    }



    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

}