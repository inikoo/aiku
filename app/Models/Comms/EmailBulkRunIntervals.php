<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 30 Nov 2024 02:16:37 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Comms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int|null $email_bulk_run_id
 * @property int $dispatched_emails_all
 * @property int $dispatched_emails_1y
 * @property int $dispatched_emails_1q
 * @property int $dispatched_emails_1m
 * @property int $dispatched_emails_1w
 * @property int $dispatched_emails_3d
 * @property int $dispatched_emails_1d
 * @property int $dispatched_emails_ytd
 * @property int $dispatched_emails_qtd
 * @property int $dispatched_emails_mtd
 * @property int $dispatched_emails_wtd
 * @property int $dispatched_emails_tdy
 * @property int $dispatched_emails_lm
 * @property int $dispatched_emails_lw
 * @property int $dispatched_emails_ld
 * @property int $dispatched_emails_1y_ly
 * @property int $dispatched_emails_1q_ly
 * @property int $dispatched_emails_1m_ly
 * @property int $dispatched_emails_1w_ly
 * @property int $dispatched_emails_3d_ly
 * @property int $dispatched_emails_1d_ly
 * @property int $dispatched_emails_ytd_ly
 * @property int $dispatched_emails_qtd_ly
 * @property int $dispatched_emails_mtd_ly
 * @property int $dispatched_emails_wtd_ly
 * @property int $dispatched_emails_tdy_ly
 * @property int $dispatched_emails_lm_ly
 * @property int $dispatched_emails_lw_ly
 * @property int $dispatched_emails_ld_ly
 * @property int $dispatched_emails_py1
 * @property int $dispatched_emails_py2
 * @property int $dispatched_emails_py3
 * @property int $dispatched_emails_py4
 * @property int $dispatched_emails_py5
 * @property int $dispatched_emails_pq1
 * @property int $dispatched_emails_pq2
 * @property int $dispatched_emails_pq3
 * @property int $dispatched_emails_pq4
 * @property int $dispatched_emails_pq5
 * @property int $opened_emails_all
 * @property int $opened_emails_1y
 * @property int $opened_emails_1q
 * @property int $opened_emails_1m
 * @property int $opened_emails_1w
 * @property int $opened_emails_3d
 * @property int $opened_emails_1d
 * @property int $opened_emails_ytd
 * @property int $opened_emails_qtd
 * @property int $opened_emails_mtd
 * @property int $opened_emails_wtd
 * @property int $opened_emails_tdy
 * @property int $opened_emails_lm
 * @property int $opened_emails_lw
 * @property int $opened_emails_ld
 * @property int $opened_emails_1y_ly
 * @property int $opened_emails_1q_ly
 * @property int $opened_emails_1m_ly
 * @property int $opened_emails_1w_ly
 * @property int $opened_emails_3d_ly
 * @property int $opened_emails_1d_ly
 * @property int $opened_emails_ytd_ly
 * @property int $opened_emails_qtd_ly
 * @property int $opened_emails_mtd_ly
 * @property int $opened_emails_wtd_ly
 * @property int $opened_emails_tdy_ly
 * @property int $opened_emails_lm_ly
 * @property int $opened_emails_lw_ly
 * @property int $opened_emails_ld_ly
 * @property int $opened_emails_py1
 * @property int $opened_emails_py2
 * @property int $opened_emails_py3
 * @property int $opened_emails_py4
 * @property int $opened_emails_py5
 * @property int $opened_emails_pq1
 * @property int $opened_emails_pq2
 * @property int $opened_emails_pq3
 * @property int $opened_emails_pq4
 * @property int $opened_emails_pq5
 * @property int $clicked_emails_all
 * @property int $clicked_emails_1y
 * @property int $clicked_emails_1q
 * @property int $clicked_emails_1m
 * @property int $clicked_emails_1w
 * @property int $clicked_emails_3d
 * @property int $clicked_emails_1d
 * @property int $clicked_emails_ytd
 * @property int $clicked_emails_qtd
 * @property int $clicked_emails_mtd
 * @property int $clicked_emails_wtd
 * @property int $clicked_emails_tdy
 * @property int $clicked_emails_lm
 * @property int $clicked_emails_lw
 * @property int $clicked_emails_ld
 * @property int $clicked_emails_1y_ly
 * @property int $clicked_emails_1q_ly
 * @property int $clicked_emails_1m_ly
 * @property int $clicked_emails_1w_ly
 * @property int $clicked_emails_3d_ly
 * @property int $clicked_emails_1d_ly
 * @property int $clicked_emails_ytd_ly
 * @property int $clicked_emails_qtd_ly
 * @property int $clicked_emails_mtd_ly
 * @property int $clicked_emails_wtd_ly
 * @property int $clicked_emails_tdy_ly
 * @property int $clicked_emails_lm_ly
 * @property int $clicked_emails_lw_ly
 * @property int $clicked_emails_ld_ly
 * @property int $clicked_emails_py1
 * @property int $clicked_emails_py2
 * @property int $clicked_emails_py3
 * @property int $clicked_emails_py4
 * @property int $clicked_emails_py5
 * @property int $clicked_emails_pq1
 * @property int $clicked_emails_pq2
 * @property int $clicked_emails_pq3
 * @property int $clicked_emails_pq4
 * @property int $clicked_emails_pq5
 * @property int $bounced_emails_all
 * @property int $bounced_emails_1y
 * @property int $bounced_emails_1q
 * @property int $bounced_emails_1m
 * @property int $bounced_emails_1w
 * @property int $bounced_emails_3d
 * @property int $bounced_emails_1d
 * @property int $bounced_emails_ytd
 * @property int $bounced_emails_qtd
 * @property int $bounced_emails_mtd
 * @property int $bounced_emails_wtd
 * @property int $bounced_emails_tdy
 * @property int $bounced_emails_lm
 * @property int $bounced_emails_lw
 * @property int $bounced_emails_ld
 * @property int $bounced_emails_1y_ly
 * @property int $bounced_emails_1q_ly
 * @property int $bounced_emails_1m_ly
 * @property int $bounced_emails_1w_ly
 * @property int $bounced_emails_3d_ly
 * @property int $bounced_emails_1d_ly
 * @property int $bounced_emails_ytd_ly
 * @property int $bounced_emails_qtd_ly
 * @property int $bounced_emails_mtd_ly
 * @property int $bounced_emails_wtd_ly
 * @property int $bounced_emails_tdy_ly
 * @property int $bounced_emails_lm_ly
 * @property int $bounced_emails_lw_ly
 * @property int $bounced_emails_ld_ly
 * @property int $bounced_emails_py1
 * @property int $bounced_emails_py2
 * @property int $bounced_emails_py3
 * @property int $bounced_emails_py4
 * @property int $bounced_emails_py5
 * @property int $bounced_emails_pq1
 * @property int $bounced_emails_pq2
 * @property int $bounced_emails_pq3
 * @property int $bounced_emails_pq4
 * @property int $bounced_emails_pq5
 * @property int $subscribed_all
 * @property int $subscribed_1y
 * @property int $subscribed_1q
 * @property int $subscribed_1m
 * @property int $subscribed_1w
 * @property int $subscribed_3d
 * @property int $subscribed_1d
 * @property int $subscribed_ytd
 * @property int $subscribed_qtd
 * @property int $subscribed_mtd
 * @property int $subscribed_wtd
 * @property int $subscribed_tdy
 * @property int $subscribed_lm
 * @property int $subscribed_lw
 * @property int $subscribed_ld
 * @property int $subscribed_1y_ly
 * @property int $subscribed_1q_ly
 * @property int $subscribed_1m_ly
 * @property int $subscribed_1w_ly
 * @property int $subscribed_3d_ly
 * @property int $subscribed_1d_ly
 * @property int $subscribed_ytd_ly
 * @property int $subscribed_qtd_ly
 * @property int $subscribed_mtd_ly
 * @property int $subscribed_wtd_ly
 * @property int $subscribed_tdy_ly
 * @property int $subscribed_lm_ly
 * @property int $subscribed_lw_ly
 * @property int $subscribed_ld_ly
 * @property int $subscribed_py1
 * @property int $subscribed_py2
 * @property int $subscribed_py3
 * @property int $subscribed_py4
 * @property int $subscribed_py5
 * @property int $subscribed_pq1
 * @property int $subscribed_pq2
 * @property int $subscribed_pq3
 * @property int $subscribed_pq4
 * @property int $subscribed_pq5
 * @property int $unsubscribed_all
 * @property int $unsubscribed_1y
 * @property int $unsubscribed_1q
 * @property int $unsubscribed_1m
 * @property int $unsubscribed_1w
 * @property int $unsubscribed_3d
 * @property int $unsubscribed_1d
 * @property int $unsubscribed_ytd
 * @property int $unsubscribed_qtd
 * @property int $unsubscribed_mtd
 * @property int $unsubscribed_wtd
 * @property int $unsubscribed_tdy
 * @property int $unsubscribed_lm
 * @property int $unsubscribed_lw
 * @property int $unsubscribed_ld
 * @property int $unsubscribed_1y_ly
 * @property int $unsubscribed_1q_ly
 * @property int $unsubscribed_1m_ly
 * @property int $unsubscribed_1w_ly
 * @property int $unsubscribed_3d_ly
 * @property int $unsubscribed_1d_ly
 * @property int $unsubscribed_ytd_ly
 * @property int $unsubscribed_qtd_ly
 * @property int $unsubscribed_mtd_ly
 * @property int $unsubscribed_wtd_ly
 * @property int $unsubscribed_tdy_ly
 * @property int $unsubscribed_lm_ly
 * @property int $unsubscribed_lw_ly
 * @property int $unsubscribed_ld_ly
 * @property int $unsubscribed_py1
 * @property int $unsubscribed_py2
 * @property int $unsubscribed_py3
 * @property int $unsubscribed_py4
 * @property int $unsubscribed_py5
 * @property int $unsubscribed_pq1
 * @property int $unsubscribed_pq2
 * @property int $unsubscribed_pq3
 * @property int $unsubscribed_pq4
 * @property int $unsubscribed_pq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Comms\EmailBulkRun|null $emailBulkRun
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailBulkRunIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailBulkRunIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailBulkRunIntervals query()
 * @mixin \Eloquent
 */
class EmailBulkRunIntervals extends Model
{
    protected $table = 'email_bulk_run_intervals';

    protected $guarded = [];

    public function emailBulkRun(): BelongsTo
    {
        return $this->belongsTo(EmailBulkRun::class);
    }
}
