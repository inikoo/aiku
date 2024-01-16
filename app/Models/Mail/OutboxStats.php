<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Mail\OutboxStats
 *
 * @property int $id
 * @property int|null $outbox_id
 * @property int $number_mailshots
 * @property int $number_dispatched_emails
 * @property int $number_dispatched_emails_state_ready
 * @property int $number_dispatched_emails_state_error
 * @property int $number_dispatched_emails_state_rejected
 * @property int $number_dispatched_emails_state_sent
 * @property int $number_dispatched_emails_state_delivered
 * @property int $number_dispatched_emails_state_hard_bounce
 * @property int $number_dispatched_emails_state_soft_bounce
 * @property int $number_dispatched_emails_state_opened
 * @property int $number_dispatched_emails_state_clicked
 * @property int $number_dispatched_emails_state_spam
 * @property int $number_dispatched_emails_state_unsubscribed
 * @property int $number_provoked_unsubscribe
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Mail\Outbox|null $outbox
 * @method static Builder|OutboxStats newModelQuery()
 * @method static Builder|OutboxStats newQuery()
 * @method static Builder|OutboxStats query()
 * @mixin Eloquent
 */
class OutboxStats extends Model
{
    protected $table = 'outbox_stats';

    protected $guarded = [];

    public function outbox(): BelongsTo
    {
        return $this->belongsTo(Outbox::class);
    }
}
