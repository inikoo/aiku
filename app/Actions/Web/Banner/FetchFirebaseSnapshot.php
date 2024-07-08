<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:15 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Portfolio\Banner;

use App\Actions\Traits\WithActionUpdate;
use App\Actions\Web\Banner\HasBannerCommand;
use App\Models\Web\Banner;
use Illuminate\Console\Command;
use Lorisleiva\Actions\ActionRequest;

class FetchFirebaseSnapshot
{
    use WithActionUpdate;
    use HasBannerCommand;


    public function handle(Banner $banner): bool
    {
        $customer  = customer();
        $database  = app('firebase.database');
        $reference = $database->getReference('customers/'.$customer->ulid.'/banner_workshop/'.$banner->slug);
        $value     = $reference->getValue();
        if ($value) {
            $modelData = [
                'layout' => $value
            ];

            UpdateUnpublishedBannerSnapshot::run($banner->unpublishedSnapshot, $modelData);

            return true;
        }

        return false;
    }


    public function asController(Banner $banner, ActionRequest $request): bool
    {
        $request->validate();

        return $this->handle($banner);
    }

    public function getCommandSignature(): string
    {
        return 'banner:fetch-from-firebase {slug}';
    }

    public function asCommand(Command $command): int
    {

        if($banner=$this->getBanner($command)) {
            $result = $this->handle($banner);
            if ($result) {
                $command->info("Done! banner  $banner->slug unpublished slide from 🔥 updated 🥳");
            } else {
                $command->error("Banner $banner->slug not found in firebase 😱");
            }
            return 0;
        }
        return 1;



    }




}
