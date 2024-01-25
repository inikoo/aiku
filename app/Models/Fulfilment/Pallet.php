<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 15:20:49 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Fulfilment;

use App\Stubs\Migrations\HasSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 *
 * @property int $id
 * @property string $slug
 * @property string $label
 */

class Pallet extends Model
{
    use HasSlug;
    use HasSoftDeletes;

    protected $guarded = [];
    protected $casts   = [
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    public function storedItems(): BelongsToMany
    {
        return $this->belongsToMany(StoredItem::class)->using(PalletStoredItem::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('label')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')->slugsShouldBeNoLongerThan(12);
    }
}
