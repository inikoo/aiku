<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:11:46 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Comms;

use App\Actions\Utils\Abbreviate;
use App\Enums\Comms\Outbox\OutboxBlueprintEnum;
use App\Enums\Comms\Outbox\OutboxStateEnum;
use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Enums\Comms\Outbox\OutboxTypeEnum;
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
 * @property OutboxCodeEnum $code
 * @property OutboxTypeEnum $type
 * @property string $name
 * @property OutboxBlueprintEnum $blueprint
 * @property OutboxStateEnum $state
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property array $sources
 * @property-read Collection<int, \App\Models\Comms\DispatchedEmail> $dispatchedEmails
 * @property-read Collection<int, \App\Models\Comms\EmailRun> $emailRuns
 * @property-read \App\Models\Comms\EmailTemplate|null $emailTemplate
 * @property-read Collection<int, \App\Models\Comms\Email> $emails
 * @property-read Fulfilment|null $fulfilment
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read Collection<int, \App\Models\Comms\Mailshot> $mailshots
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Comms\PostRoom|null $postRoom
 * @property-read Shop|null $shop
 * @property-read \App\Models\Comms\OutboxStats|null $stats
 * @property-read Collection<int, \App\Models\Comms\ModelSubscribedToOutbox> $subscribers
 * @property-read Collection<int, \App\Models\Comms\ModelSubscribedToOutbox> $unsubscribed
 * @property-read Website|null $website
 * @method static \Database\Factories\Comms\OutboxFactory factory($count = null, $state = [])
 * @method static Builder<static>|Outbox newModelQuery()
 * @method static Builder<static>|Outbox newQuery()
 * @method static Builder<static>|Outbox onlyTrashed()
 * @method static Builder<static>|Outbox query()
 * @method static Builder<static>|Outbox withTrashed()
 * @method static Builder<static>|Outbox withoutTrashed()
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
        'sources' => 'array',
        'code'  => OutboxCodeEnum::class,
        'type'  => OutboxTypeEnum::class,
        'state' => OutboxStateEnum::class,
        'blueprint' => OutboxBlueprintEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
        'sources' => '{}'
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                if ($this->type == 'reorder-reminder') {
                    $abbreviation = 'ror';
                } else {
                    $abbreviation = Abbreviate::run(string:$this->type->value, maximumLength:6);
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

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    public function emailRuns(): HasMany
    {
        return $this->hasMany(EmailRun::class);
    }

    public function mailshots(): HasMany
    {
        return $this->hasMany(Mailshot::class);
    }

    public function dispatchedEmails(): HasMany
    {
        return $this->hasMany(DispatchedEmail::class);
    }

    public function emailTemplate(): HasOne
    {
        return $this->hasOne(EmailTemplate::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function fulfilment(): BelongsTo
    {
        return $this->belongsTo(Fulfilment::class);
    }

    public function postRoom(): BelongsTo
    {
        return $this->belongsTo(PostRoom::class);
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(ModelSubscribedToOutbox::class)
                    ->whereNull('unsubscribed_at');
    }

    public function unsubscribed(): HasMany
    {
        return $this->hasMany(ModelSubscribedToOutbox::class)
                    ->whereNotNull('unsubscribed_at');
    }
}