<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Production;

use App\Enums\Production\JobOrderItemTask\JobOrderItemTaskStateEnum;
use App\Models\Production\ArtefactManufactureTask;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $job_order_slug
 * @property string $production_slug
 * @property string $organisation_slug
 * @property string $reference
 * @property string $batch_code
 * @property string $job_order_state
 * @property string|null $job_order_deleted_at
 * @property string|null $artisan
 * @property string|null $date
 * @property string|null $started_at
 * @property string|null $last_worked_at
 * @property int $quantity
 * @property string|null $quantity_made
 * @property string|null $last_task_state
 * @property string|null $last_task_units_per_artefact
 * @property string $quantity_rejected
 * @property string $quantity_received
 * @property int $number_tasks
 * @property int $number_tasks_done
 * @property string|null $carried_from_reference
 * @property string|null $carried_to_reference
 * @property array|null $steps
 */
class ArtefactBatchesResource extends JsonResource
{
    public function toArray($request): array
    {
        $status = $this->status();

        return [
            'id'                     => $this->id,
            'job_order_route'        => [
                'name'       => 'grp.org.productions.show.operations.job-orders.show',
                'parameters' => [$this->organisation_slug, $this->production_slug, $this->job_order_slug],
            ],
            'reference'              => $this->reference,
            'batch_code'             => $this->batch_code,
            'status'                 => $status,
            'status_label'           => [
                'cancelled'   => __('Cancelled'),
                'received'    => __('Received into stock'),
                'finished'    => __('Finished'),
                'in_progress' => __('In progress'),
                'to_do'       => __('To do'),
            ][$status],
            'artisan'                => $this->artisan,
            'date'                   => $this->date,
            'started_at'             => $this->started_at,
            'finished_at'            => in_array($status, ['finished', 'received']) ? $this->last_worked_at : null,
            'quantity'               => (int) $this->quantity,
            'quantity_made'          => ArtefactManufactureTask::artefactsIn((float) $this->quantity_made, $this->last_task_units_per_artefact ?: 1),
            'quantity_rejected'      => (float) $this->quantity_rejected,
            'quantity_received'      => (float) $this->quantity_received,
            'number_tasks'           => (int) $this->number_tasks,
            'number_tasks_done'      => (int) $this->number_tasks_done,
            'carried_from_reference' => $this->carried_from_reference,
            'carried_to_reference'   => $this->carried_to_reference,
            'steps'                  => $this->steps,
        ];
    }

    private function status(): string
    {
        if ($this->job_order_deleted_at) {
            return 'cancelled';
        }
        if ($this->last_task_state == JobOrderItemTaskStateEnum::DONE->value) {
            return (float) $this->quantity_received > 0 ? 'received' : 'finished';
        }

        return $this->started_at ? 'in_progress' : 'to_do';
    }
}
