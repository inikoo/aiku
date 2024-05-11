<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\UI;

use App\Models\Ordering\Order;
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
