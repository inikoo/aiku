<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 29 Dec 2024 00:54:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\OrgPaymentServiceProvider;

use App\Actions\HydrateModel;
use App\Actions\Accounting\OrgPaymentServiceProvider\Hydrators\OrgPaymentServiceProviderHydratePayments;
use App\Models\Accounting\OrgPaymentServiceProvider;
use Illuminate\Support\Collection;

class HydrateOrgPaymentServiceProvider extends HydrateModel
{
    public string $commandSignature = 'hydrate:org_payment_service_provider {slugs?*} {--o|org=*}  {--g|group=*}   ';

    public function handle(OrgPaymentServiceProvider $orgPaymentServiceProvider): void
    {
        OrgPaymentServiceProviderHydratePayments::run($orgPaymentServiceProvider);
    }


    protected function getModel(string $slug): OrgPaymentServiceProvider
    {
        return OrgPaymentServiceProvider::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return OrgPaymentServiceProvider::get();
    }
}
