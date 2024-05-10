<?php

namespace App\Http\Resources\Manufacturing;

use Illuminate\Http\Resources\Json\JsonResource;

class JobOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
         /** @var JobOrder $jobOrder */
        $jobOrder = $this;
        return [
            'reference' => $jobOrder->reference,
            'state' => $jobOrder->state,
            'date' => $jobOrder->date,
            'notes' => [
                'customer' => $jobOrder->customer_notes,
                'public' => $jobOrder->public_notes,
                'internal' => $jobOrder->internal_notes,
            ],
        ];
    }
}
