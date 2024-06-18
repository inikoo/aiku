<?php


namespace App\Http\Resources\CRM;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $code
 * @property string $name

 */
class FilteredProductsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'slug'               => $this->slug,
            'code'               => $this->code,
            'name'               => $this->name,
        ];
    }
}
