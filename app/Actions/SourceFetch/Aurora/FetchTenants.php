<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 29 Apr 2023 14:31:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\WithTenantSource;
use App\Models\Tenancy\Tenant;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchTenants
{
    use AsAction;
    use WithTenantSource;


    public function handle(SourceTenantService $tenantSource, Tenant $tenant): Tenant
    {
        $tenantData = $tenantSource->fetchTenant($tenant);
        $tenant->update(
            $tenantData['tenant']
        );
        $accountsServiceProviderData = Db::connection('aurora')->table('Payment Service Provider Dimension')
            ->select('Payment Service Provider Key')
            ->where('Payment Service Provider Block', 'Accounts')->first();

        if ($accountsServiceProviderData) {
            $tenant->execute(fn (Tenant $tenant) => $tenant->accountsServiceProvider()->update(
                [
                    'source_id' => $accountsServiceProviderData->{'Payment Service Provider Key'}
                ]
            ));
        }

        return $tenant;
    }


    public string $commandSignature = 'fetch:tenants';

    public function asCommand(Command $command): int
    {

        Tenant::all()->eachCurrent(function (Tenant $tenant) use ($command) {
            $tenantSource = $this->getTenantSource($tenant);
            $tenantSource->initialisation(app('currentTenant'));
            $tenant = $this->handle($tenantSource, $tenant);
            if ($tenant->created_at->lt($tenant->group->created_at)) {
                $tenant->group->created_at=$tenant->created_at;
                $tenant->group->save();
            }
        });



        return 0;
    }


}
