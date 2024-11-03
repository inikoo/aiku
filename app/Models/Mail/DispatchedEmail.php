<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 Nov 2023 08:44:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use App\Models\CRM\Customer;
use App\Models\CRM\Prospect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

/**
 *
 *
 * @property int $id
 * @property int|null $outbox_id
 * @property int|null $mailshot_id
 * @property int|null $email_address_id
 * @property string|null $ses_id
 * @property string|null $recipient_type
 * @property int|null $recipient_id
 * @property DispatchedEmailStateEnum $state
 * @property string|null $sent_at
 * @property string|null $first_read_at
 * @property string|null $last_read_at
 * @property string|null $first_clicked_at
 * @property string|null $last_clicked_at
 * @property int $number_reads
 * @property int $number_clicks
 * @property bool $mask_as_spam
 * @property bool $provoked_unsubscribe
 * @property array $data
 * @property bool $is_test
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property string|null $source_id
 * @property-read \App\Models\Mail\EmailAddress|null $emailAddresses
 * @property-read \App\Models\Mail\Mailshot|null $mailshot
 * @property-read \App\Models\Mail\Outbox|null $outbox
 * @property-read Model|\Eloquent|null $recipient
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispatchedEmail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispatchedEmail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DispatchedEmail query()
 * @mixin \Eloquent
 */
class DispatchedEmail extends Model
{
    protected $casts = [
        'data'  => 'array',
        'state' => DispatchedEmailStateEnum::class,
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function emailAddress(): BelongsTo
    {
        return $this->belongsTo(EmailAddress::class);
    }


    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }


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
        if ($this->is_test) {
            return Auth::user()->contact_name;
        }

        if ($this->recipient) {
            /** @var Prospect|Customer $recipient */
            $recipient = $this->recipient;

            return $recipient->name;
        }

        return '';
    }

}
