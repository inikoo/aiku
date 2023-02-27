<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 12:08:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant\Hydrators;

use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Central\Tenant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;


class TenantHydrateAccounting implements ShouldBeUnique
{

    use AsAction;

    public function handle(Tenant $tenant): void
    {
        $stats = [
            'number_payment_service_providers' => PaymentServiceProvider::count(),
            'number_payment_accounts'          => PaymentAccount::count(),
            'number_payments'                  => Payment::count()
        ];

        $tenant->accountingStats()->update($stats);
    }

    public function getJobUniqueId(Tenant $tenant): string
    {
        return $tenant->id;
    }


}


