<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 09 Dec 2023 02:13:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\Hydrators;

use App\Http\Resources\SysAdmin\UserSearchResultResource;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UserHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(User $user): void
    {
        $user->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $user->group_id,
                'sections'          => ['sysadmin'],
                'haystack_tier_1'   => $user->username,
                'haystack_tier_2'   => trim($user->email.' '.$user->contact_name),
                'result'            => [
                    'title'      => $user->username,
                    'icon'       => [
                        'icon' => 'fal fa-terminal'
                    ],
                    'meta'       => UserSearchResultResource::make($user)
                ]
            ]
        );
    }


}
