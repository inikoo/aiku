<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 30 Nov 2024 01:08:10 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Comms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int|null $outbox_id
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
 * @property int $dispatched_emails_all_ly
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
 * @property int $opened_emails_all_ly
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
 * @property int $clicked_emails_all_ly
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
 * @property int $unsubscribed_emails_all
 * @property int $unsubscribed_emails_1y
 * @property int $unsubscribed_emails_1q
 * @property int $unsubscribed_emails_1m
 * @property int $unsubscribed_emails_1w
 * @property int $unsubscribed_emails_3d
 * @property int $unsubscribed_emails_1d
 * @property int $unsubscribed_emails_ytd
 * @property int $unsubscribed_emails_qtd
 * @property int $unsubscribed_emails_mtd
 * @property int $unsubscribed_emails_wtd
 * @property int $unsubscribed_emails_tdy
 * @property int $unsubscribed_emails_lm
 * @property int $unsubscribed_emails_lw
 * @property int $unsubscribed_emails_ld
 * @property int $unsubscribed_emails_all_ly
 * @property int $unsubscribed_emails_1y_ly
 * @property int $unsubscribed_emails_1q_ly
 * @property int $unsubscribed_emails_1m_ly
 * @property int $unsubscribed_emails_1w_ly
 * @property int $unsubscribed_emails_3d_ly
 * @property int $unsubscribed_emails_1d_ly
 * @property int $unsubscribed_emails_ytd_ly
 * @property int $unsubscribed_emails_qtd_ly
 * @property int $unsubscribed_emails_mtd_ly
 * @property int $unsubscribed_emails_wtd_ly
 * @property int $unsubscribed_emails_tdy_ly
 * @property int $unsubscribed_emails_lm_ly
 * @property int $unsubscribed_emails_lw_ly
 * @property int $unsubscribed_emails_ld_ly
 * @property int $unsubscribed_emails_py1
 * @property int $unsubscribed_emails_py2
 * @property int $unsubscribed_emails_py3
 * @property int $unsubscribed_emails_py4
 * @property int $unsubscribed_emails_py5
 * @property int $unsubscribed_emails_pq1
 * @property int $unsubscribed_emails_pq2
 * @property int $unsubscribed_emails_pq3
 * @property int $unsubscribed_emails_pq4
 * @property int $unsubscribed_emails_pq5
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
 * @property int $bounced_emails_all_ly
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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Comms\Outbox|null $outbox
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboxIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboxIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboxIntervals query()
 * @mixin \Eloquent
 */
class OutboxIntervals extends Model
{
    protected $table = 'outbox_intervals';

    protected $guarded = [];

    public function outbox(): BelongsTo
    {
        return $this->belongsTo(Outbox::class);
    }
}
