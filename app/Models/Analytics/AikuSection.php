<?php
/*
 * author Arya Permana - Kirin
 * created on 21-11-2024-10h-39m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Analytics;

use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class AikuSection extends Model
{
    use InGroup;
    use HasSlug;

    protected $guarded = [
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
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(64);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shops(): MorphToMany
    {
        return $this->morphedByMany(Shop::class, 'model', 'aiku_section_has_models')->withTimestamps();
    }
    
    public function fulfilments(): MorphToMany
    {
        return $this->morphedByMany(Fulfilment::class, 'model', 'aiku_section_has_models')->withTimestamps();
    }
    
    public function warehouses(): MorphToMany
    {
        return $this->morphedByMany(Warehouse::class, 'model', 'aiku_section_has_models')->withTimestamps();
    }
    
    public function productions(): MorphToMany
    {
        return $this->morphedByMany(Production::class, 'model', 'aiku_section_has_models')->withTimestamps();
    }
    
    public function organisations(): MorphToMany
    {
        return $this->morphedByMany(Organisation::class, 'model', 'aiku_section_has_models')->withTimestamps();
    }
    
    public function groups(): MorphToMany
    {
        return $this->morphedByMany(Group::class, 'model', 'aiku_section_has_models')->withTimestamps();
    }
}
