<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 15 Dec 2021 20:03:40 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Http\Resources\Utils;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;


class ActionResultResource extends JsonResource
{

    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var \App\Models\Utils\ActionResult $actionResult */
        $actionResult = $this;

        return [
            'model'   => [
                'id'   => $actionResult->model_id,
                'name' => $actionResult->model ? class_basename($actionResult->model::class) : null,
                'data' => $actionResult->model ?: null,
            ],
            'action'=>[
                'type'=>$actionResult->status,
                'changes' => $actionResult->changes,
            ]

        ];
    }
}
