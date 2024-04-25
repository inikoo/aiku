<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 22:40:53 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Sales;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $code
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property string $name
 * @property string $state
 * @property string $shop_slug
 * @property string $date
 * @property string $number

 *
 */
class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            // 'route'         => [
            //     'name'       => 'grp.org.hr.employees.index',
            //     'parameters' => $employee->slug
            // ],
            'title'         => $this->name,
            'subtitle'      => $this->slug,
            'label1'        => $this->number,
            'label2'        => $this->slug,
            // 'icon'          => ['fal', 'fa-user-hard-hat']


            // 'slug'       => $this->slug,
            // 'number'     => $this->number,
            // 'date'       => $this->date,
            // 'name'       => $this->name,
            // 'state'      => $this->state,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            // 'shop_slug'  => $this->shop_slug,
        ];
    }
}
