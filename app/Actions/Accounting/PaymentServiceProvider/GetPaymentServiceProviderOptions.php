<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider;

use App\Actions\InertiaAction;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsObject;

class GetPaymentServiceProviderOptions extends InertiaAction
{
    use AsObject;

    public function handle(Organisation|Group $parent): array
    {
        $selectOptions = [];
        /** @var PaymentServiceProvider $paymentServiceProvider */
        foreach ($parent->paymentServiceProviders as $paymentServiceProvider) {
            $selectOptions[$paymentServiceProvider->id] =
                [
                    'slug' => $paymentServiceProvider->slug,
                    'name' => $paymentServiceProvider->name
                ];
        }

        return $selectOptions;
    }
}
