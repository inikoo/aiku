<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 21:56:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin;

use App\Models\SysAdmin\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use JsonSerializable;

class ProfileResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var User $user */
        $user = $this;

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
            'settings'         => [
                'language'  => $user->language_id,
                'app_theme' => Arr::get($user->settings, 'app_theme')
            ]
        ];
    }
}
