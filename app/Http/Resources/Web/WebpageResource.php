<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jul 2023 12:17:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Web;

use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Http\Resources\HasSelfCall;
use App\Models\Web\Webpage;
use Illuminate\Http\Resources\Json\JsonResource;

class WebpageResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var Webpage $webpage */
        $webpage = $this;

        return [
            'id'              => $webpage->id,
            'slug'            => $webpage->slug,
            'level'           => $webpage->level,
            'code'            => $webpage->code,
            'url'             => $webpage->url,
            'type'            => $webpage->type,
            'typeIcon'        => match ($webpage->type) {
                WebpageTypeEnum::STOREFRONT => ['fal', 'fa-home'],
                WebpageTypeEnum::ENGAGEMENT => ['fal', 'fa-ufo-beam'],
                WebpageTypeEnum::AUTH       => ['fal', 'fa-sign-in'],
                WebpageTypeEnum::BLOG       => ['fal', 'fa-newspaper'],
                default                     => ['fal', 'fa-browser']
            },
            'is_dirty'            => $webpage->is_dirty,
            'compiled_layout'     => $webpage->compiled_layout ?: [],
            'purpose'             => $webpage->purpose,
            'created_at'          => $webpage->created_at,
            'updated_at'          => $webpage->updated_at,
            'state'               => $webpage->state,
            'images_upload_route' => [
                'name'       => 'org.models.webpage.images.store',
                'parameters' => $webpage->id
            ],
            'set_as_ready_route'   => [
                'name'       => 'org.models.webpage.content.publish',
                'parameters' => $webpage->id
            ],
            'update_route'       => [
                'name'       => 'grp.models.webpage.content.update',
                'parameters' => $webpage->id
            ],
        ];
    }
}
