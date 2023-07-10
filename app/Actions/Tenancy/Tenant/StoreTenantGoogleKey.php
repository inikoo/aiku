<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant;

use App\Models\Tenancy\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTenantGoogleKey
{
    use AsAction;
    use WithAttributes;

    public function handle(Tenant $tenant, array $modelData): Tenant
    {
        $tenant->update([
            'data' => json_encode([
                'google_cloud_client_id'     => $modelData['google_cloud_client_id'],
                'google_cloud_client_secret' => $modelData['google_cloud_client_secret']
            ])
        ]);

        return $tenant;
    }

    public function action(Tenant $tenant, $modelData): Tenant
    {
        return $this->handle($tenant, $modelData);
    }
}
