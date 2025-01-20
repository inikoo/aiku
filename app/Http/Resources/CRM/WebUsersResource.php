<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Feb 2024 12:51:58 Malaysia Time, Madrid Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\CRM;

use App\Actions\Utils\GetLocationFromIp;
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
    public function toArray($request): array
    {


        return [
            'slug'        => $this->slug,
            'username'    => $this->username,
            'image'         => $this->imageSources(48, 48),
            'location'      => GetLocationFromIp::run($this->last_login_ip),
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
