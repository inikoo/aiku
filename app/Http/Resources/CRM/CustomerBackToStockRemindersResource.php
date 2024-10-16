<?php
/*
 * author Arya Permana - Kirin
 * created on 16-10-2024-10h-59m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Http\Resources\CRM;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $ulid
 * @property string $reference
 * @property string $name
 * @property string $contact_name
 * @property string $company_name
 * @property string $email
 * @property string $phone
 * @property string $created_at
 * @property string $updated_at
 */
class CustomerBackToStockRemindersResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'slug'                   => $this->slug,
            'name'                   => $this->name,
            'description'            => $this->description,
            'price'                  => $this->price,
        ];
    }
}
