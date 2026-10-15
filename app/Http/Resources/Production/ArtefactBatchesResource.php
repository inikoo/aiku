<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Production;

use App\Enums\Production\JobOrder\JobOrderStateEnum;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $job_order_slug
 * @property string $production_slug
 * @property string $organisation_slug
 * @property string $reference
 * @property string $job_order_state
 * @property string|null $artisan
 * @property string|null $date
 * @property int $quantity
 * @property string|null $quantity_made
 * @property string $quantity_received
 * @property int $number_tasks
 * @property int $number_tasks_done
 */
class ArtefactBatchesResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'job_order_route'   => [
                'name'       => 'grp.org.productions.show.operations.job-orders.show',
                'parameters' => [$this->organisation_slug, $this->production_slug, $this->job_order_slug],
            ],
            'reference'         => $this->reference,
            'state_label'       => JobOrderStateEnum::labels()[$this->job_order_state] ?? $this->job_order_state,
            'artisan'           => $this->artisan,
            'date'              => $this->date,
            'quantity'          => (int) $this->quantity,
            'quantity_made'     => (float) $this->quantity_made,
            'quantity_received' => (float) $this->quantity_received,
            'number_tasks'      => (int) $this->number_tasks,
            'number_tasks_done' => (int) $this->number_tasks_done,
        ];
    }
}
