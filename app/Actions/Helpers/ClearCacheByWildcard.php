<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 03 Feb 2025 17:16:43 Malaysia Time, Plane, KL-Bali
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers;

use Cache;
use Illuminate\Support\Facades\Redis;
use Lorisleiva\Actions\Concerns\AsAction;

class ClearCacheByWildcard
{
    use AsAction;


    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle(string $pattern): void
    {
        $keys = Redis::connection('cache')->scan('0', [
            'match' => config('database.redis.options.prefix').config('cache.prefix').
                $pattern,
            'count' => 1000000,
        ]);
        if ($keys) {
            foreach ($keys[1] as $key) {
                $key = str_replace(config('database.redis.options.prefix').config('cache.prefix'), '', $key);
                Cache::delete($key);
            }
        }
    }


}
