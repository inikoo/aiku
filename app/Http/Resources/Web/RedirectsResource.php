<?php
/*
 * author Arya Permana - Kirin
 * created on 12-03-2025-11h-45m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Http\Resources\Web;

use App\Http\Resources\Helpers\ImageResource;
use App\Models\Web\Slide;
use Illuminate\Http\Resources\Json\JsonResource;

class RedirectsResource extends JsonResource
{
    public function toArray($request): array
    {
        $redirect = $this;

        return [
            'id'         => $redirect->id,
            'url'       => $redirect->url,
            'path'     => $redirect->path,
            'type' => $redirect->type,
            'webpage_title' => $redirect->webpage_title
        ];
    }
}
