<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping;

use App\Models\Dropshipping\Portfolio;
use Illuminate\Support\Facades\Cache;

trait WithSingleUploadProgress
{
    /**
     * A product created from the Retina row is queued as an upload of one, so the job broadcasts
     * its result on the channel's upload progress like a bulk upload and the page refreshes the row.
     *
     * @return array{cache_key: string, total: int}
     */
    protected function singleUploadProgress(Portfolio $portfolio): array
    {
        $cacheKey = 'upload_progress_'.$portfolio->customer_sales_channel_id.'_'.uniqid();
        Cache::put($cacheKey.'_success', 0, now()->addDay());
        Cache::put($cacheKey.'_fail', 0, now()->addDay());

        return [
            'cache_key' => $cacheKey,
            'total'     => 1,
        ];
    }
}
