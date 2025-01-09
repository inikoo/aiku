<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Feb 2024 12:51:58 Malaysia Time, Madrid Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\CRM;

use App\Models\CRM\WebUser;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class WebUsersResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var WebUser $webUser */
        $webUser = $this;

        return [
            'slug'        => $webUser->slug,
            'username'    => $webUser->username,
            'status'        => match ($webUser->status) {
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
            'email'       => $webUser->email,
            'is_root'     => $webUser->is_root,
            'created_at'  => $webUser->created_at,
            'last_active' => $webUser->last_active
                                ? Carbon::parse($webUser->last_active)->diffForHumans()
                                : ''
            ];
    }
}
