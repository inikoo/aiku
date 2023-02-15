<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 15 Feb 2023 17:11:34 Malaysia Time, Ubud , Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\CustomerClient;

use App\Models\Dropshipping\CustomerClient;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCustomerClient
{
    use AsAction;

    public function handle(CustomerClient $customerClient): ?bool
    {
        return $customerClient->delete();
    }
}
