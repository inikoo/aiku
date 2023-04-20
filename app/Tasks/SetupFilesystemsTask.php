<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 18:37:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Tasks;

use App\Models\Central\CentralMedia;
use App\Models\Media\Media;
use Illuminate\Support\Facades\Storage;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SetupFilesystemsTask implements SwitchTenantTask
{
    public function makeCurrent(Tenant $tenant): void
    {
        $this->setTenantFilesystem($tenant);
    }


    public function forgetCurrent(): void
    {
        $this->setTenantFilesystem();
    }

    protected function setTenantFilesystem(?Tenant $tenant = null): void
    {
        Storage::forgetDisk(['tenant','tenant_public']);


        if ($tenant) {
            config()->set('media-library.media_model', Media::class);

            config()->set(
                'filesystems.disks.tenant.root',
                storage_path('app/tenant/'.$tenant->code)
            );
            config()->set(
                'filesystems.disks.tenant_public.root',
                storage_path('app/public/tenant/'.$tenant->code)
            );
        } else {
            config()->set('media-library.media_model', CentralMedia::class);

            config()->set(
                'filesystems.disks.tenant.root',
                storage_path('app/central')
            );
            config()->set(
                'filesystems.disks.tenant_public.root',
                storage_path('app/public/central')
            );
        }
    }
}
