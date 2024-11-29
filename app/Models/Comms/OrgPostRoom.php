<?php
/*
 * author Arya Permana - Kirin
 * created on 29-11-2024-16h-57m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\Comms;

use App\Enums\Comms\PostRoom\PostRoomCodeEnum;
use App\Models\SysAdmin\Group;
use App\Models\Traits\InOrganisation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Mail\Mail
 *
 * @property int $id
 * @property int $group_id
 * @property string $slug
 * @property PostRoomCodeEnum $code
 * @property string $name
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Group $group
 * @property-read Collection<int, \App\Models\Comms\Outbox> $outboxes
 * @property-read \App\Models\Comms\PostRoomStats|null $stats
 * @method static Builder<static>|PostRoom newModelQuery()
 * @method static Builder<static>|PostRoom newQuery()
 * @method static Builder<static>|PostRoom query()
 * @mixin Eloquent
 */
class OrgPostRoom extends Model
{
    use HasSlug;
    use InOrganisation;

    protected $casts = [
        'code' => PostRoomCodeEnum::class,
        'data' => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->code->value.'-'.$this->group->slug;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function stats(): HasOne
    {
        return $this->hasOne(PostRoomStats::class);
    }

    public function outboxes(): HasMany
    {
        return $this->hasMany(Outbox::class);
    }

    public function postRoom(): HasOne
    {
        return $this->hasOne(PostRoom::class);
    }
}
