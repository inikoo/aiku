<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 27 Oct 2023 19:32:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Actions\Utils\Abbreviate;
use App\Enums\Mail\Mailshot\MailshotStateEnum;
use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Models\Market\Shop;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Mail\Mailshot
 *
 * @property int $id
 * @property string $slug
 * @property string $subject
 * @property \App\Enums\Mail\Mailshot\MailshotStateEnum $state
 * @property \Illuminate\Support\Carbon|null $schedule_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Enums\Mail\Mailshot\MailshotTypeEnum $type
 * @property string $date
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
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $delete_comment
 * @property string|null $recipients_stored_at
 * @property int|null $outbox_id
 * @property int|null $email_template_id
 * @property array|null $data
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mail\MailshotSendChannel> $channels
 * @property-read int|null $channels_count
 * @property-read \App\Models\Mail\MailshotStats|null $mailshotStats
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \App\Models\Media\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Mail\Outbox|null $outbox
 * @property-read Model|\Eloquent $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mail\MailshotRecipient> $recipients
 * @property-read int|null $recipients_count
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot query()
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereDeleteComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereEmailTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereLayout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereOutboxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereParentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot wherePublisherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereReadyAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereRecipientsRecipe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereRecipientsStoredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereScheduleAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereStartSendingAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereStoppedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Mailshot withoutTrashed()
 * @mixin \Eloquent
 */
class Mailshot extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use HasSlug;
    use InteractsWithMedia;

    protected $casts = [
        'recipients_recipe' => 'array',
        'layout'            => 'array',
        'data'              => 'array',
        'type'              => MailshotTypeEnum::class,
        'state'             => MailshotStateEnum::class,
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return Abbreviate::run(string: $this->subject, maximumLength: 16).' '.Abbreviate::run($this->type->value, 4);
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(16);
    }


    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MailshotRecipient::class);
    }

    public function mailshotStats(): HasOne
    {
        return $this->hasOne(MailshotStats::class);
    }

    public function sender()
    {
        if (app()->environment('production')) {
            /** @var Shop $parent */
            $parent = $this->parent;
            $sender = $parent->prospectsSenderEmail->email_address;
        } else {
            $sender = config('mail.devel.sender_email_address');
        }

        return $sender;
    }

    public function channels(): HasMany
    {
        return $this->hasMany(MailshotSendChannel::class);
    }

    public function outbox(): BelongsTo
    {
        return $this->belongsTo(Outbox::class);
    }


}
