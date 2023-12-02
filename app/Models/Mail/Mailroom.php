<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Mar 2023 16:46:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Enums\Mail\Mailroom\MailroomTypeEnum;
use App\Models\Grouping\Group;
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
 * @property MailroomTypeEnum $type
 * @property string $name
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Group $group
 * @property-read Collection<int, \App\Models\Mail\Outbox> $outboxes
 * @property-read \App\Models\Mail\MailroomStats|null $stats
 * @method static Builder|Mailroom newModelQuery()
 * @method static Builder|Mailroom newQuery()
 * @method static Builder|Mailroom query()
 * @mixin Eloquent
 */
class Mailroom extends Model
{
    use HasSlug;

    protected $casts = [
        'type' => MailroomTypeEnum::class,
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
                return $this->type->value.'-'.$this->group->slug;
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
        return $this->hasOne(MailroomStats::class);
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
