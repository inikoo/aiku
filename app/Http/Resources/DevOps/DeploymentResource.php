<?php

namespace App\Http\Resources\DevOps;

use Illuminate\Http\Resources\Json\JsonResource;

/***
 *
 * @property $version
 * @property $hash
 * @property $state
 * @property $data
 *
 */
class DeploymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'version' => $this->version,
            'hash'    => $this->hash,
            'state'   => $this->state,
            'data'    => $this->data
        ];
    }
}
