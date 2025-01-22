<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Feb 2024 12:51:58 Malaysia Time, Madrid Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\CRM;

use App\Actions\SysAdmin\WithLogRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

/**
 * @property mixed $slug
 * @property mixed $username
 * @property mixed $status
 * @property mixed $email
 * @property mixed $is_root
 * @property mixed $created_at
 * @property mixed $last_active
 */
class WebUsersResource extends JsonResource
{
    use WithLogRequest;
    public function toArray($request): array
    {

        $location = json_decode($this->last_location);
        return [
            'slug'        => $this->slug,
            'username'    => $this->username,
            'image'         => $this->imageSources(48, 48),
            'server'       => $location == ['localhost'],
            'last_location'      => $location,
            'last_device'      => array_filter([
                $this->last_device ? [
                    'tooltip' => $this->last_device,
                    'icon' => $this->getDeviceIcon($this->last_device)
                ] : null,
                $this->last_os ? [
                    'tooltip' => $this->last_os,
                    'icon'  => $this->getPlatformIcon($this->last_os)
                ] : null,
            ]),
            'status'       => $this->status,
            'status_icon'        => match ($this->status) {
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
            'contact_name'       => $this->contact_name,
            'is_root'     => $this->is_root,
            'root_icon'   => $this->is_root ? [
                'tooltip' => __('Root User'),
                'icon'    => 'fal fa-crown',
                'class'   => 'text-yellow-500'

            ] : null,
            'created_at'  => $this->created_at,
            'last_active' => $this->last_active
                                ? Carbon::parse($this->last_active)->diffForHumans()
                                : ''
            ];
    }
}
