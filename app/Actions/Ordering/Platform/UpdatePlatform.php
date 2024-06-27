<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Platform;

use App\Actions\GrpAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Ordering\Platform;
use App\Models\SysAdmin\Group;
use Illuminate\Validation\Rule;

class UpdatePlatform extends GrpAction
{
    use WithActionUpdate;

    public function handle(Platform $platform, array $modelData): Platform
    {
        /** @var Platform $platform */
        $platform = $this->update($platform, $modelData);

        return $platform;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required', Rule::unique('platforms', 'code')],
            'name' => ['sometimes', 'required']
        ];
    }

    public function action(Group $group, Platform $platform, array $modelData): Platform
    {
        $this->initialisation($group, $modelData);

        return $this->handle($platform, $modelData);
    }
}
