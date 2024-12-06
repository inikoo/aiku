<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Nov 2023 16:16:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\CRM;

use App\Http\Resources\Tag\TagResource;
use App\Models\CRM\Prospect;
use App\Models\Helpers\Tag;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $code
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $contact_website
 * @property \Spatie\Tags\Tag $tags
 */
class ProspectsResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Prospect $prospect */
        $prospect = $this;

        return [
            'id'         => $prospect->id,
            'slug'       => $prospect->slug,
            'name'       => $prospect->name,
            'email'      => $this->email,
            'phone'      => $prospect->phone,
            'website'    => $prospect->contact_website,
            'tags'       => $prospect->tags()->pluck('slug')->toArray(),
            'state'      => $prospect->state,
            'state_icon' => $prospect->state->stateIcon()[$prospect->state->value],

            'tagRoute'   => [
                'store' => [
                    'name'       => 'grp.models.prospect.tag.store',
                    'parameters' => [
                        'prospect' => $prospect->id
                    ]
                ],
                'update' => [
                    'name'       => 'grp.models.prospect.tag.attach',
                    'parameters' => [
                        'prospect' => $prospect->id
                    ]
                ],
            ],
            'tagsList'    => TagResource::collection(Tag::where('type', 'crm')->get()),
        ];
    }
}
