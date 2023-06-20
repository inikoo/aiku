<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 08:40:50 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Assets;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 *
 * @property string $code
 * @property string $name
 * @property int $id
 * @property string $original_name
 *
 */
class LanguageResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'code'          => $this->code,
            'name'          => $this->name,
            'original_name' => $this->original_name,

        ];
    }

}
