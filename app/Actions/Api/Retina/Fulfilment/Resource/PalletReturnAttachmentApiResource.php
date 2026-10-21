<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Api\Retina\Fulfilment\Resource;

use App\Models\Helpers\Media;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Media
 */
class PalletReturnAttachmentApiResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'scope'      => $this->pivot?->scope,
            'caption'    => $this->pivot?->caption,
            'file_name'  => $this->file_name,
            'mime_type'  => $this->mime_type,
            'size'       => $this->size,
            'created_at' => $this->created_at,
        ];
    }
}
