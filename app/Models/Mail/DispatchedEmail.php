<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 Nov 2023 08:44:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Models\CRM\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

/**
 * App\Models\Mail\DispatchedEmail
 *
 * @property int $id
 * @property int|null $outbox_id
 * @property int|null $mailshot_id
 * @property int $email_id
 * @property string|null $recipient_type
 * @property int|null $recipient_id
 * @property string|null $provider_message_id
 * @property \App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum $state
 * @property bool $is_error
 * @property bool $is_rejected
 * @property bool $is_sent
 * @property bool $is_delivered
 * @property bool $is_hard_bounced
 * @property bool $is_soft_bounced
 * @property bool $is_opened
 * @property bool $is_clicked
 * @property bool $is_spam
 * @property bool $is_unsubscribed
 * @property string|null $sent_at
 * @property string|null $delivered_at
 * @property string $date
 * @property bool $is_test
 * @property array $data
 * @property string $ulid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Mail\Email $email
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Mail\DispatchedEmailEvent> $events
 * @property-read int|null $events_count
 * @property-read \App\Models\Mail\Mailshot|null $mailshot
 * @property-read \App\Models\Mail\MailshotRecipient|null $mailshotRecipient
 * @property-read \App\Models\Mail\Outbox|null $outbox
 * @property-read Model|\Eloquent $recipient
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail query()
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereEmailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereIsClicked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereIsDelivered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereIsError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereIsHardBounced($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereIsOpened($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereIsRejected($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereIsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereIsSoftBounced($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereIsSpam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereIsTest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereIsUnsubscribed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereMailshotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereOutboxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereProviderMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereRecipientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereRecipientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereUlid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DispatchedEmail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DispatchedEmail extends Model
{
    protected $casts = [
        'data'        => 'array',
        'state'       => \App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum::class,
    ];

    protected $attributes = [
        'data'     => '{}',
    ];

    protected $guarded = [];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    /*    public function mailshotRecipient(): HasOne
        {
            return $this->hasOne(MailshotRecipient::class);
        }*/

    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    /*    public function events(): HasMany
        {
            return $this->hasMany(DispatchedEmailEvent::class);

        }*/

    public function mailshot(): BelongsTo
    {
        return $this->belongsTo(Mailshot::class);
    }

    public function outbox(): BelongsTo
    {
        return $this->belongsTo(Outbox::class);
    }

    public function getName(): string
    {

        if($this->is_test) {
            return Auth::user()->contact_name;
        }

        if($this->recipient) {
            /** @var Prospect|Customer $recipient */
            $recipient=$this->recipient;
            return $recipient->name;
        }
        return '';
    }

}
