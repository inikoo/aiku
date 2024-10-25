<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Actions\Utils\Abbreviate;
use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Traits\HasHistory;
use App\Models\Traits\InShop;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Mail\Mailshot
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property string $slug
 * @property string $subject
 * @property int|null $outbox_id
 * @property int|null $email_template_id
 * @property MailshotStateEnum $state
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $ready_at
 * @property \Illuminate\Support\Carbon|null $start_sending_at
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $stopped_at
 * @property array $layout
 * @property array $recipients_recipe
 * @property int|null $publisher_id org user
 * @property string $parent_type
 * @property int $parent_id
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $source_id
 * @property MailshotTypeEnum $type
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read Collection<int, \App\Models\Mail\DispatchedEmail> $dispatchedEmails
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Mail\Outbox|null $outbox
 * @property-read Model|\Eloquent $parent
 * @property-read Collection<int, \App\Models\Mail\MailshotRecipient> $recipients
 * @property-read Shop|null $shop
 * @property-read \App\Models\Mail\MailshotStats|null $stats
 * @method static \Database\Factories\Mail\MailshotFactory factory($count = null, $state = [])
 * @method static Builder<static>|Mailshot newModelQuery()
 * @method static Builder<static>|Mailshot newQuery()
 * @method static Builder<static>|Mailshot onlyTrashed()
 * @method static Builder<static>|Mailshot query()
 * @method static Builder<static>|Mailshot withTrashed()
 * @method static Builder<static>|Mailshot withoutTrashed()
 * @mixin Eloquent
 */
class Mailshot extends Model implements Auditable
{
    use SoftDeletes;
    use HasFactory;
    use InShop;
    use HasHistory;

    protected $casts = [
        'recipients_recipe' => 'array',
        'layout'            => 'array',
        'data'              => 'array',
        'type'              => MailshotTypeEnum::class,
        'state'             => MailshotStateEnum::class,
        'date'              => 'datetime',
        'sent_at'           => 'datetime',
        'schedule_at'       => 'datetime',
        'ready_at'          => 'datetime',
        'cancelled_at'      => 'datetime',
        'stopped_at'        => 'datetime',
        'start_sending_at'  => 'datetime',
    ];

    protected $attributes = [
        'layout'            => '{}',
        'data'              => '{}',
        'recipients_recipe' => '{}',
    ];


    protected $guarded = [];

    public function generateTags(): array
    {
        return [
            'marketing'
        ];
    }

    protected array $auditInclude = [
        'subject',
        'schedule_at',
    ];


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return Abbreviate::run(string: $this->type, maximumLength: 6).' '.$this->date->format('Y-m-d');
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(64);
    }

    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MailshotRecipient::class);
    }

    public function outbox(): BelongsTo
    {
        return $this->belongsTo(Outbox::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(MailshotStats::class);
    }

    public function dispatchedEmails(): HasMany
    {
        return $this->hasMany(DispatchedEmail::class);
    }


}
