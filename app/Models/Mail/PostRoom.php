<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Mar 2023 16:46:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Enums\Mail\PostRoom\PostRoomCodeEnum;
use App\Models\SysAdmin\Group;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Group $group
 * @property-read Collection<int, \App\Models\Mail\Outbox> $outboxes
 * @property-read \App\Models\Mail\PostRoomStats|null $stats
 * @method static Builder|PostRoom newModelQuery()
 * @method static Builder|PostRoom newQuery()
 * @method static Builder|PostRoom query()
 * @mixin Eloquent
 */
class PostRoom extends Model
{
    use HasSlug;

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

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
