<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 18:37:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Tasks;

use App\Models\Central\CentralMedia;
use App\Models\Media\GroupMedia;
use Illuminate\Support\Facades\Storage;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

class SetupFilesystemsTask implements SwitchTenantTask
{
    public function makeCurrent(Tenant $organisation): void
    {
        $this->setTenantFilesystem($organisation);
    }


    public function forgetCurrent(): void
    {
        $this->setTenantFilesystem();
    }

    protected function setTenantFilesystem(?Tenant $organisation = null): void
    {
        Storage::forgetDisk(['tenant','tenant_public']);


        if ($organisation) {
            config()->set('media-library.media_model', GroupMedia::class);

            config()->set(
                'filesystems.disks.group.root',
                storage_path('app/group/'.$organisation->group->slug)
            );
            config()->set(
                'filesystems.disks.group_public.root',
                storage_path('app/public/group/'.$organisation->group->slug)
            );
        } else {
            config()->set('media-library.media_model', CentralMedia::class);

            config()->set(
                'filesystems.disks.group.root',
                storage_path('app/central')
            );
            config()->set(
                'filesystems.disks.group_public.root',
                storage_path('app/public/central')
            );
        }
    }
}
