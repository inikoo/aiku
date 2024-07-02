<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Actions\Utils\Abbreviate;
use App\Enums\Mail\Outbox\OutboxStateEnum;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Traits\InShop;
use App\Models\Web\Website;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Mail\Outbox
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $post_room_id
 * @property int|null $shop_id
 * @property int|null $website_id
 * @property int|null $fulfilment_id
 * @property string $slug
 * @property OutboxTypeEnum $type
 * @property string $name
 * @property string $blueprint
 * @property OutboxStateEnum $state
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read Collection<int, \App\Models\Mail\DispatchedEmail> $dispatchedEmails
 * @property-read Collection<int, \App\Models\Mail\EmailTemplate> $emailTemplates
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read Collection<int, \App\Models\Mail\Mailshot> $mailshots
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read Shop|null $shop
 * @property-read \App\Models\Mail\OutboxStats|null $stats
 * @method static \Database\Factories\Mail\OutboxFactory factory($count = null, $state = [])
 * @method static Builder|Outbox newModelQuery()
 * @method static Builder|Outbox newQuery()
 * @method static Builder|Outbox onlyTrashed()
 * @method static Builder|Outbox query()
 * @method static Builder|Outbox withTrashed()
 * @method static Builder|Outbox withoutTrashed()
 * @mixin Eloquent
 */
class Outbox extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasFactory;
    use InShop;

    protected $table = 'outboxes';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $casts = [
        'data'  => 'array',
        'type'  => OutboxTypeEnum::class,
        'state' => OutboxStateEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                if ($this->type == 'reorder-reminder') {
                    $abbreviation = 'ror';
                } else {
                    $abbreviation = Abbreviate::run($this->type->value);
                }
                if ($this->shop_id) {
                    $abbreviation .= ' '.$this->shop->slug;
                }

                return $abbreviation;
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(64);
    }


    public function stats(): HasOne
    {
        return $this->hasOne(OutboxStats::class);
    }

    public function mailshots(): HasMany
    {
        return $this->hasMany(Mailshot::class);
    }

    public function dispatchedEmails(): HasMany
    {
        return $this->hasMany(DispatchedEmail::class);
    }

    public function emailTemplates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }
}
