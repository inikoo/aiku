<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Dec 2024 22:58:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Enums\Comms\Outbox\OutboxCodeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Website;
use Illuminate\Support\Arr;

trait WithOutboxBuilder
{
    public function getDefaultBuilder(OutboxCodeEnum $case, Organisation|Shop|Fulfilment|Website $model): ?string
    {
        $builder = $case->defaultBuilder();
        if (!$builder and $case != OutboxCodeEnum::TEST) {
            $builder = Arr::get(
                $model->group->settings,
                'default_outbox_builder',
                config('app.default_outbox_builder')
            );
        }
        return $builder;
    }

}
