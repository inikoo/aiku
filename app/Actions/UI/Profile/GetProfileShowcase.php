<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 May 2023 20:59:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Profile;

use App\Http\Resources\HumanResources\EmployeeResource;
use App\Http\Resources\SysAdmin\Group\GroupResource;
use App\Http\Resources\SysAdmin\GuestResource;
use App\Http\Resources\SysAdmin\Organisation\UserOrganisationResource;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsObject;

class GetProfileShowcase
{
    use AsObject;

    public function handle(User $user): array
    {
        return [
            'id'            => $user->id,
            'username'      => $user->username,
            'avatar'        => $user->avatarImageSources(48, 48),
            'email'         => $user->email,
            'about'         => $user->about,
            'status'        => match ($user->status) {
                true => [
                    'tooltip' => __('active'),
                    'icon'    => 'fal fa-check',
                    'class'   => 'text-green-500'
                ],
                default => [
                    'tooltip' => __('suspended'),
                    'icon'    => 'fal fa-times',
                    'class'   => 'text-red-500'
                ]
            },
            'parent_type'   => $user->parent_type,
            'contact_name'  => $user->contact_name,
            'parent'        => match ($user->parent_type) {
                'Employee' => new EmployeeResource($user->parent),
                'Guest'    => new GuestResource($user->parent),
                default    => [],
            },
            'group'         => GroupResource::make($user->group),
            'organisations' => UserOrganisationResource::collectionForUser($user->authorisedOrganisations, $user),
            'created_at'    => $user->created_at,
            // 'updated_at'    => $user->updated_at,
            'roles'         => $user->getRoleNames()->toArray(),
            'permissions'   => $user->getAllPermissions()->pluck('name')->toArray()
        ];
    }
}
