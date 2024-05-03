<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 19:55:22 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Market\Shop;

use App\Models\Market\Shop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $shop_id
 * @property string $newsletters_all
 * @property string $newsletters_1y
 * @property string $newsletters_1q
 * @property string $newsletters_1m
 * @property string $newsletters_1w
 * @property string $newsletters_ytd
 * @property string $newsletters_qtd
 * @property string $newsletters_mtd
 * @property string $newsletters_wtd
 * @property string $newsletters_lm
 * @property string $newsletters_lw
 * @property string $newsletters_yda
 * @property string $newsletters_tdy
 * @property string $newsletters_all_ly
 * @property string $newsletters_1y_ly
 * @property string $newsletters_1q_ly
 * @property string $newsletters_1m_ly
 * @property string $newsletters_1w_ly
 * @property string $newsletters_ytd_ly
 * @property string $newsletters_qtd_ly
 * @property string $newsletters_mtd_ly
 * @property string $newsletters_wtd_ly
 * @property string $newsletters_lm_ly
 * @property string $newsletters_lw_ly
 * @property string $newsletters_yda_ly
 * @property string $newsletters_tdy_ly
 * @property string $newsletters_py1
 * @property string $newsletters_py2
 * @property string $newsletters_py3
 * @property string $newsletters_py4
 * @property string $newsletters_py5
 * @property string $newsletters_pq1
 * @property string $newsletters_pq2
 * @property string $newsletters_pq3
 * @property string $newsletters_pq4
 * @property string $newsletters_pq5
 * @property string $marketing_emails_all
 * @property string $marketing_emails_1y
 * @property string $marketing_emails_1q
 * @property string $marketing_emails_1m
 * @property string $marketing_emails_1w
 * @property string $marketing_emails_ytd
 * @property string $marketing_emails_qtd
 * @property string $marketing_emails_mtd
 * @property string $marketing_emails_wtd
 * @property string $marketing_emails_lm
 * @property string $marketing_emails_lw
 * @property string $marketing_emails_yda
 * @property string $marketing_emails_tdy
 * @property string $marketing_emails_all_ly
 * @property string $marketing_emails_1y_ly
 * @property string $marketing_emails_1q_ly
 * @property string $marketing_emails_1m_ly
 * @property string $marketing_emails_1w_ly
 * @property string $marketing_emails_ytd_ly
 * @property string $marketing_emails_qtd_ly
 * @property string $marketing_emails_mtd_ly
 * @property string $marketing_emails_wtd_ly
 * @property string $marketing_emails_lm_ly
 * @property string $marketing_emails_lw_ly
 * @property string $marketing_emails_yda_ly
 * @property string $marketing_emails_tdy_ly
 * @property string $marketing_emails_py1
 * @property string $marketing_emails_py2
 * @property string $marketing_emails_py3
 * @property string $marketing_emails_py4
 * @property string $marketing_emails_py5
 * @property string $marketing_emails_pq1
 * @property string $marketing_emails_pq2
 * @property string $marketing_emails_pq3
 * @property string $marketing_emails_pq4
 * @property string $marketing_emails_pq5
 * @property string $abandoned_carts_all
 * @property string $abandoned_carts_1y
 * @property string $abandoned_carts_1q
 * @property string $abandoned_carts_1m
 * @property string $abandoned_carts_1w
 * @property string $abandoned_carts_ytd
 * @property string $abandoned_carts_qtd
 * @property string $abandoned_carts_mtd
 * @property string $abandoned_carts_wtd
 * @property string $abandoned_carts_lm
 * @property string $abandoned_carts_lw
 * @property string $abandoned_carts_yda
 * @property string $abandoned_carts_tdy
 * @property string $abandoned_carts_all_ly
 * @property string $abandoned_carts_1y_ly
 * @property string $abandoned_carts_1q_ly
 * @property string $abandoned_carts_1m_ly
 * @property string $abandoned_carts_1w_ly
 * @property string $abandoned_carts_ytd_ly
 * @property string $abandoned_carts_qtd_ly
 * @property string $abandoned_carts_mtd_ly
 * @property string $abandoned_carts_wtd_ly
 * @property string $abandoned_carts_lm_ly
 * @property string $abandoned_carts_lw_ly
 * @property string $abandoned_carts_yda_ly
 * @property string $abandoned_carts_tdy_ly
 * @property string $abandoned_carts_py1
 * @property string $abandoned_carts_py2
 * @property string $abandoned_carts_py3
 * @property string $abandoned_carts_py4
 * @property string $abandoned_carts_py5
 * @property string $abandoned_carts_pq1
 * @property string $abandoned_carts_pq2
 * @property string $abandoned_carts_pq3
 * @property string $abandoned_carts_pq4
 * @property string $abandoned_carts_pq5
 * @property string $total_mailshots_all
 * @property string $total_mailshots_1y
 * @property string $total_mailshots_1q
 * @property string $total_mailshots_1m
 * @property string $total_mailshots_1w
 * @property string $total_mailshots_ytd
 * @property string $total_mailshots_qtd
 * @property string $total_mailshots_mtd
 * @property string $total_mailshots_wtd
 * @property string $total_mailshots_lm
 * @property string $total_mailshots_lw
 * @property string $total_mailshots_yda
 * @property string $total_mailshots_tdy
 * @property string $total_mailshots_all_ly
 * @property string $total_mailshots_1y_ly
 * @property string $total_mailshots_1q_ly
 * @property string $total_mailshots_1m_ly
 * @property string $total_mailshots_1w_ly
 * @property string $total_mailshots_ytd_ly
 * @property string $total_mailshots_qtd_ly
 * @property string $total_mailshots_mtd_ly
 * @property string $total_mailshots_wtd_ly
 * @property string $total_mailshots_lm_ly
 * @property string $total_mailshots_lw_ly
 * @property string $total_mailshots_yda_ly
 * @property string $total_mailshots_tdy_ly
 * @property string $total_mailshots_py1
 * @property string $total_mailshots_py2
 * @property string $total_mailshots_py3
 * @property string $total_mailshots_py4
 * @property string $total_mailshots_py5
 * @property string $total_mailshots_pq1
 * @property string $total_mailshots_pq2
 * @property string $total_mailshots_pq3
 * @property string $total_mailshots_pq4
 * @property string $total_mailshots_pq5
 * @property string $total_emails_all
 * @property string $total_emails_1y
 * @property string $total_emails_1q
 * @property string $total_emails_1m
 * @property string $total_emails_1w
 * @property string $total_emails_ytd
 * @property string $total_emails_qtd
 * @property string $total_emails_mtd
 * @property string $total_emails_wtd
 * @property string $total_emails_lm
 * @property string $total_emails_lw
 * @property string $total_emails_yda
 * @property string $total_emails_tdy
 * @property string $total_emails_all_ly
 * @property string $total_emails_1y_ly
 * @property string $total_emails_1q_ly
 * @property string $total_emails_1m_ly
 * @property string $total_emails_1w_ly
 * @property string $total_emails_ytd_ly
 * @property string $total_emails_qtd_ly
 * @property string $total_emails_mtd_ly
 * @property string $total_emails_wtd_ly
 * @property string $total_emails_lm_ly
 * @property string $total_emails_lw_ly
 * @property string $total_emails_yda_ly
 * @property string $total_emails_tdy_ly
 * @property string $total_emails_py1
 * @property string $total_emails_py2
 * @property string $total_emails_py3
 * @property string $total_emails_py4
 * @property string $total_emails_py5
 * @property string $total_emails_pq1
 * @property string $total_emails_pq2
 * @property string $total_emails_pq3
 * @property string $total_emails_pq4
 * @property string $total_emails_pq5
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Shop $shop
 * @method static \Illuminate\Database\Eloquent\Builder|ShopMailshotsIntervals newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopMailshotsIntervals newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShopMailshotsIntervals query()
 * @mixin \Eloquent
 */
class ShopMailshotsIntervals extends Model
{
    protected $table = 'shop_mailshots_intervals';

    protected $guarded = [];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
