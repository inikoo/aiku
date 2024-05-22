<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:22:54 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Actions\GrpAction;
use App\Actions\Traits\Actions\WithActionButtons;
use App\Actions\UI\WithInertia;
use App\Enums\UI\SysAdmin\ProfileTabsEnum;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowProfilePageHeadTabs extends GrpAction
{
    use AsAction;
    use WithInertia;
    use WithActionButtons;

    public function asController(ActionRequest $request): User
    {
        $this->initialisation(group(), $request)->withTab(ProfileTabsEnum::values());

        return $request->user();
    }

    public function jsonResponse(User $user): array
    {
        return [
            "pageHead"                       => [
                "title"        => __("My Profile"),
                'actions'      => [
                    [
                        'type'  => 'button',
                        'style' => 'edit',
                        'label' => __('edit profile'),
                        'route' => [
                            'name'       => 'grp.profile.edit',
                            'parameters' => array_values(request()->route()->originalParameters())
                        ],
                    ]
                ]
            ],
            'tabs'                           => [
                'current'    => $this->tab,
                'navigation' => ProfileTabsEnum::navigation()
            ],

        ];
    }
}
