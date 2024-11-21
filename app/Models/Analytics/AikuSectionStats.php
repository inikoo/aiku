<?php
/*
 * author Arya Permana - Kirin
 * created on 21-11-2024-10h-49m
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

class AikuSectionStats extends Model
{
    protected $table = 'aiku_section_stats';

    protected $guarded = [
    ];

    public function aikuSection(): BelongsTo
    {
        return $this->belongsTo(AikuSection::class);
    }
}
