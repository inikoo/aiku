<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 26 Nov 2024 20:58:56 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Billables\Rental\UI;

use App\Models\Billables\Rental;
use Lorisleiva\Actions\Concerns\AsObject;

class GetRentalShowcase
{
    use AsObject;

    public function handle(Rental $rental): array
    {
        return [
            []
        ];
    }
}
