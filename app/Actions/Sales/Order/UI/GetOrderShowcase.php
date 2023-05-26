<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Order\UI;

use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOrderShowcase
{
    use AsObject;

    public function handle(Order $order): array
    {
        return [
            []
        ];
    }
}
