<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 24 Nov 2024 10:35:14 Central Indonesia Time, Sanur, Kuta, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Comms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int|null $email_run_id
 * @property int $number_dispatched_emails
 * @property int $number_dispatched_emails_state_ready
 * @property int $number_dispatched_emails_state_sent_to_provider
 * @property int $number_dispatched_emails_state_error
 * @property int $number_dispatched_emails_state_rejected_by_provider
 * @property int $number_dispatched_emails_state_sent
 * @property int $number_dispatched_emails_state_delivered
 * @property int $number_dispatched_emails_state_hard_bounce
 * @property int $number_dispatched_emails_state_soft_bounce
 * @property int $number_dispatched_emails_state_opened
 * @property int $number_dispatched_emails_state_clicked
 * @property int $number_dispatched_emails_state_spam
 * @property int $number_dispatched_emails_state_unsubscribed
 * @property int $number_provoked_unsubscribe
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Comms\EmailRun|null $emailRun
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailRunStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailRunStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailRunStats query()
 * @mixin \Eloquent
 */
class EmailRunStats extends Model
{
    protected $table = 'email_run_stats';

    protected $guarded = [];

    public function emailRun(): BelongsTo
    {
        return $this->belongsTo(EmailRun::class);
    }
}
