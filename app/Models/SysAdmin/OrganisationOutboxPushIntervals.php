<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 31 Dec 2024 23:12:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $organisation_id
 * @property int $push_dispatched_emails_all
 * @property int $push_dispatched_emails_1y
 * @property int $push_dispatched_emails_1q
 * @property int $push_dispatched_emails_1m
 * @property int $push_dispatched_emails_1w
 * @property int $push_dispatched_emails_3d
 * @property int $push_dispatched_emails_1d
 * @property int $push_dispatched_emails_ytd
 * @property int $push_dispatched_emails_qtd
 * @property int $push_dispatched_emails_mtd
 * @property int $push_dispatched_emails_wtd
 * @property int $push_dispatched_emails_tdy
 * @property int $push_dispatched_emails_lm
 * @property int $push_dispatched_emails_lw
 * @property int $push_dispatched_emails_ld
 * @property int $push_dispatched_emails_1y_ly
 * @property int $push_dispatched_emails_1q_ly
 * @property int $push_dispatched_emails_1m_ly
 * @property int $push_dispatched_emails_1w_ly
 * @property int $push_dispatched_emails_3d_ly
 * @property int $push_dispatched_emails_1d_ly
 * @property int $push_dispatched_emails_ytd_ly
 * @property int $push_dispatched_emails_qtd_ly
 * @property int $push_dispatched_emails_mtd_ly
 * @property int $push_dispatched_emails_wtd_ly
 * @property int $push_dispatched_emails_tdy_ly
 * @property int $push_dispatched_emails_lm_ly
 * @property int $push_dispatched_emails_lw_ly
 * @property int $push_dispatched_emails_ld_ly
 * @property int $push_dispatched_emails_py1
 * @property int $push_dispatched_emails_py2
 * @property int $push_dispatched_emails_py3
 * @property int $push_dispatched_emails_py4
 * @property int $push_dispatched_emails_py5
 * @property int $push_dispatched_emails_pq1
 * @property int $push_dispatched_emails_pq2
 * @property int $push_dispatched_emails_pq3
 * @property int $push_dispatched_emails_pq4
 * @property int $push_dispatched_emails_pq5
 * @property int $push_opened_emails_all
 * @property int $push_opened_emails_1y
 * @property int $push_opened_emails_1q
 * @property int $push_opened_emails_1m
 * @property int $push_opened_emails_1w
 * @property int $push_opened_emails_3d
 * @property int $push_opened_emails_1d
 * @property int $push_opened_emails_ytd
 * @property int $push_opened_emails_qtd
 * @property int $push_opened_emails_mtd
 * @property int $push_opened_emails_wtd
 * @property int $push_opened_emails_tdy
 * @property int $push_opened_emails_lm
 * @property int $push_opened_emails_lw
 * @property int $push_opened_emails_ld
 * @property int $push_opened_emails_1y_ly
 * @property int $push_opened_emails_1q_ly
 * @property int $push_opened_emails_1m_ly
 * @property int $push_opened_emails_1w_ly
 * @property int $push_opened_emails_3d_ly
 * @property int $push_opened_emails_1d_ly
 * @property int $push_opened_emails_ytd_ly
 * @property int $push_opened_emails_qtd_ly
 * @property int $push_opened_emails_mtd_ly
 * @property int $push_opened_emails_wtd_ly
 * @property int $push_opened_emails_tdy_ly
 * @property int $push_opened_emails_lm_ly
 * @property int $push_opened_emails_lw_ly
 * @property int $push_opened_emails_ld_ly
 * @property int $push_opened_emails_py1
 * @property int $push_opened_emails_py2
 * @property int $push_opened_emails_py3
 * @property int $push_opened_emails_py4
 * @property int $push_opened_emails_py5
 * @property int $push_opened_emails_pq1
 * @property int $push_opened_emails_pq2
 * @property int $push_opened_emails_pq3
 * @property int $push_opened_emails_pq4
 * @property int $push_opened_emails_pq5
 * @property int $push_clicked_emails_all
 * @property int $push_clicked_emails_1y
 * @property int $push_clicked_emails_1q
 * @property int $push_clicked_emails_1m
 * @property int $push_clicked_emails_1w
 * @property int $push_clicked_emails_3d
 * @property int $push_clicked_emails_1d
 * @property int $push_clicked_emails_ytd
 * @property int $push_clicked_emails_qtd
 * @property int $push_clicked_emails_mtd
 * @property int $push_clicked_emails_wtd
 * @property int $push_clicked_emails_tdy
 * @property int $push_clicked_emails_lm
 * @property int $push_clicked_emails_lw
 * @property int $push_clicked_emails_ld
 * @property int $push_clicked_emails_1y_ly
 * @property int $push_clicked_emails_1q_ly
 * @property int $push_clicked_emails_1m_ly
 * @property int $push_clicked_emails_1w_ly
 * @property int $push_clicked_emails_3d_ly
 * @property int $push_clicked_emails_1d_ly
 * @property int $push_clicked_emails_ytd_ly
 * @property int $push_clicked_emails_qtd_ly
 * @property int $push_clicked_emails_mtd_ly
 * @property int $push_clicked_emails_wtd_ly
 * @property int $push_clicked_emails_tdy_ly
 * @property int $push_clicked_emails_lm_ly
 * @property int $push_clicked_emails_lw_ly
 * @property int $push_clicked_emails_ld_ly
 * @property int $push_clicked_emails_py1
 * @property int $push_clicked_emails_py2
 * @property int $push_clicked_emails_py3
 * @property int $push_clicked_emails_py4
 * @property int $push_clicked_emails_py5
 * @property int $push_clicked_emails_pq1
 * @property int $push_clicked_emails_pq2
 * @property int $push_clicked_emails_pq3
 * @property int $push_clicked_emails_pq4
 * @property int $push_clicked_emails_pq5
 * @property int $push_bounced_emails_all
 * @property int $push_bounced_emails_1y
 * @property int $push_bounced_emails_1q
 * @property int $push_bounced_emails_1m
 * @property int $push_bounced_emails_1w
 * @property int $push_bounced_emails_3d
 * @property int $push_bounced_emails_1d
 * @property int $push_bounced_emails_ytd
 * @property int $push_bounced_emails_qtd
 * @property int $push_bounced_emails_mtd
 * @property int $push_bounced_emails_wtd
 * @property int $push_bounced_emails_tdy
 * @property int $push_bounced_emails_lm
 * @property int $push_bounced_emails_lw
 * @property int $push_bounced_emails_ld
 * @property int $push_bounced_emails_1y_ly
 * @property int $push_bounced_emails_1q_ly
 * @property int $push_bounced_emails_1m_ly
 * @property int $push_bounced_emails_1w_ly
 * @property int $push_bounced_emails_3d_ly
 * @property int $push_bounced_emails_1d_ly
 * @property int $push_bounced_emails_ytd_ly
 * @property int $push_bounced_emails_qtd_ly
 * @property int $push_bounced_emails_mtd_ly
 * @property int $push_bounced_emails_wtd_ly
 * @property int $push_bounced_emails_tdy_ly
 * @property int $push_bounced_emails_lm_ly
 * @property int $push_bounced_emails_lw_ly
 * @property int $push_bounced_emails_ld_ly
 * @property int $push_bounced_emails_py1
 * @property int $push_bounced_emails_py2
 * @property int $push_bounced_emails_py3
 * @property int $push_bounced_emails_py4
 * @property int $push_bounced_emails_py5
 * @property int $push_bounced_emails_pq1
 * @property int $push_bounced_emails_pq2
 * @property int $push_bounced_emails_pq3
 * @property int $push_bounced_emails_pq4
 * @property int $push_bounced_emails_pq5
 * @property int $push_subscribed_all
 * @property int $push_subscribed_1y
 * @property int $push_subscribed_1q
 * @property int $push_subscribed_1m
 * @property int $push_subscribed_1w
 * @property int $push_subscribed_3d
 * @property int $push_subscribed_1d
 * @property int $push_subscribed_ytd
 * @property int $push_subscribed_qtd
 * @property int $push_subscribed_mtd
 * @property int $push_subscribed_wtd
 * @property int $push_subscribed_tdy
 * @property int $push_subscribed_lm
 * @property int $push_subscribed_lw
 * @property int $push_subscribed_ld
 * @property int $push_subscribed_1y_ly
 * @property int $push_subscribed_1q_ly
 * @property int $push_subscribed_1m_ly
 * @property int $push_subscribed_1w_ly
 * @property int $push_subscribed_3d_ly
 * @property int $push_subscribed_1d_ly
 * @property int $push_subscribed_ytd_ly
 * @property int $push_subscribed_qtd_ly
 * @property int $push_subscribed_mtd_ly
 * @property int $push_subscribed_wtd_ly
 * @property int $push_subscribed_tdy_ly
 * @property int $push_subscribed_lm_ly
 * @property int $push_subscribed_lw_ly
 * @property int $push_subscribed_ld_ly
 * @property int $push_subscribed_py1
 * @property int $push_subscribed_py2
 * @property int $push_subscribed_py3
 * @property int $push_subscribed_py4
 * @property int $push_subscribed_py5
 * @property int $push_subscribed_pq1
 * @property int $push_subscribed_pq2
 * @property int $push_subscribed_pq3
 * @property int $push_subscribed_pq4
 * @property int $push_subscribed_pq5
 * @property int $push_unsubscribed_all
 * @property int $push_unsubscribed_1y
 * @property int $push_unsubscribed_1q
 * @property int $push_unsubscribed_1m
 * @property int $push_unsubscribed_1w
 * @property int $push_unsubscribed_3d
 * @property int $push_unsubscribed_1d
 * @property int $push_unsubscribed_ytd
 * @property int $push_unsubscribed_qtd
 * @property int $push_unsubscribed_mtd
 * @property int $push_unsubscribed_wtd
 * @property int $push_unsubscribed_tdy
 * @property int $push_unsubscribed_lm
 * @property int $push_unsubscribed_lw
 * @property int $push_unsubscribed_ld
 * @property int $push_unsubscribed_1y_ly
 * @property int $push_unsubscribed_1q_ly
 * @property int $push_unsubscribed_1m_ly
 * @property int $push_unsubscribed_1w_ly
 * @property int $push_unsubscribed_3d_ly
 * @property int $push_unsubscribed_1d_ly
 * @property int $push_unsubscribed_ytd_ly
 * @property int $push_unsubscribed_qtd_ly
 * @property int $push_unsubscribed_mtd_ly
 * @property int $push_unsubscribed_wtd_ly
 * @property int $push_unsubscribed_tdy_ly
 * @property int $push_unsubscribed_lm_ly
 * @property int $push_unsubscribed_lw_ly
 * @property int $push_unsubscribed_ld_ly
 * @property int $push_unsubscribed_py1
 * @property int $push_unsubscribed_py2
 * @property int $push_unsubscribed_py3
 * @property int $push_unsubscribed_py4
 * @property int $push_unsubscribed_py5
 * @property int $push_unsubscribed_pq1
 * @property int $push_unsubscribed_pq2
 * @property int $push_unsubscribed_pq3
 * @property int $push_unsubscribed_pq4
 * @property int $push_unsubscribed_pq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationOutboxPushIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationOutboxPushIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrganisationOutboxPushIntervals query()
 * @mixin \Eloquent
 */
class OrganisationOutboxPushIntervals extends Model
{
    protected $table = 'organisation_outbox_push_intervals';

    protected $guarded = [];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
