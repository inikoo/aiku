<?php
/*
 *  Author: Jonathan lopez <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, inikoo
 */

namespace App\Http\Resources\UniversalSearch;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $model_type
 * @property mixed $result
 * @property mixed $model_id
 */
class UniversalSearchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'model_type' => $this->model_type,
            'model_id'   => $this->model_id,
            'result'     => $this->result,
        ];
    }
}
