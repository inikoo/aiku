<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Jun 2023 16:25:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant;

use App\Models\Tenancy\Tenant;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SetTenantLogo
{
    use AsAction;

    public function handle(): void
    {

        $tenant=app('currentTenant');
        try {
            $seed       = 'tenant-'.$tenant->id;
            $groupMedia = $tenant->addMediaFromUrl("https://api.dicebear.com/6.x/shapes/svg?seed=$seed")
                ->preservingOriginal()
                ->usingFileName($tenant->slug."-logo.sgv")
                ->toMediaCollection('logo', 'group');

            $logoId = $groupMedia->id;

            $tenant->update(['logo_id' => $logoId]);
        } catch(Exception) {
            //
        }
    }


    public string $commandSignature = 'maintenance:reset-tenant-logo {tenant : Tenant slug}';

    public function asCommand(Command $command): int
    {

        try {
            $tenant=Tenant::where('slug', $command->argument('tenant'))->firstOrFail();
        } catch (Exception) {
            $command->error('Tenant not found');
            return 1;
        }

        $tenant->makeCurrent();

        $this->handle();

        return 0;
    }
}
