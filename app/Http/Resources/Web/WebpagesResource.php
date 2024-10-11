<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 14:30:36 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Web;

use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $slug
 * @property mixed $level
 * @property mixed $code
 * @property mixed $url
 * @property mixed $type
 * @property mixed $sub_type
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property mixed $state
 */
class WebpagesResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {



        return [
            'id'                  => $this->id,
            'slug'                => $this->slug,
            'level'               => $this->level,
            'code'                => $this->code,
            'url'                 => $this->url,
            'type'                => $this->type,
            'typeIcon'            => match ($this->type) {
                WebpageTypeEnum::STOREFRONT => ['fal', 'fa-home'],
                WebpageTypeEnum::OPERATIONS => ['fal', 'fa-ufo-beam'],
                WebpageTypeEnum::BLOG       => ['fal', 'fa-newspaper'],
                default                     => ['fal', 'fa-browser']
            },
            'sub_type'             => $this->sub_type,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
            'state'               => $this->state,
        ];
    }
}
