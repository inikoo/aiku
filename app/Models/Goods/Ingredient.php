<?php
/*
 * author Arya Permana - Kirin
 * created on 28-10-2024-10h-17m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Goods;

use App\Models\SysAdmin\Group;
use App\Models\Traits\HasHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @property int $number_trade_units
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Group $group
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Goods\TradeUnit> $tradeUnits
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Ingredient query()
 * @mixin \Eloquent
 */
class Ingredient extends Model implements Auditable
{
    use HasSlug;
    use HasHistory;

    protected $guarded = [];

    public function generateTags(): array
    {
        return [
            'goods'
        ];
    }

    protected array $auditInclude = [
        'name',
        'number_trade_units',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(TradeUnit::class, 'trade_unit_has_ingredients');
    }
}
