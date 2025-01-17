<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Feb 2024 12:51:58 Malaysia Time, Madrid Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\CRM;

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
            'status'        => match ($this->status) {
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
            'email'       => $this->email,
            'is_root'     => $this->is_root,
            'created_at'  => $this->created_at,
            'last_active' => $this->last_active
                                ? Carbon::parse($this->last_active)->diffForHumans()
                                : ''
            ];
    }
}
