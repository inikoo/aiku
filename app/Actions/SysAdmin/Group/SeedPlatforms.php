<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 19 Jun 2024 18:03:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group;

use App\Actions\Ordering\Platform\StorePlatform;
use App\Actions\Traits\WithAttachMediaToModel;
use App\Enums\Ordering\Platform\PlatformTypeEnum;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedPlatforms
{
    use AsAction;
    use WithAttachMediaToModel;

    public function handle(): void
    {
        $types = PlatformTypeEnum::values();

        foreach ($types as $type) {
            StorePlatform::make()->action([
                'code' => $type,
                'name' => Str::ucfirst($type)
            ]);
        }
    }


    public string $commandSignature = 'platforms:seed';

    public function asCommand(): int
    {
        $this->handle();
        echo "Success seed the platforms ✅ \n";

        return 0;
    }
}
