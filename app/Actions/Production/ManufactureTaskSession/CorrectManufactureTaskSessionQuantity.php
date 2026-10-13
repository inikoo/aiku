<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026 10:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureTaskSession;

use App\Actions\OrgAction;
use App\Actions\Production\JobOrderItem\UpdateJobOrderItem;
use App\Actions\Production\JobOrderItemTask\CalculateJobOrderItemTaskQuantities;
use App\Enums\Production\JobOrder\JobOrderStateEnum;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum;
use App\Models\Production\ArtefactManufactureTask;
use App\Models\Production\JobOrderItemTask;
use App\Models\Production\ManufactureTaskSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class CorrectManufactureTaskSessionQuantity extends OrgAction
{
    private ?string $correctedBy = null;

    /**
     * A finished entry typed too high is brought down to what was really made. Whatever the job
     * grew by on a manager's authorisation shrinks back with it, and the pay is worked out again.
     */
    public function handle(ManufactureTaskSession $session, array $modelData): ManufactureTaskSession
    {
        return DB::transaction(function () use ($session, $modelData) {
            $session  = ManufactureTaskSession::lockForUpdate()->find($session->id);
            $quantity = (float) $modelData['quantity_made'];
            $previous = (float) $session->quantity_made;

            if ($session->state != ManufactureTaskSessionStateEnum::CLOSED || $session->isNonProductive()) {
                $this->refuse(__('Only a finished production entry can be corrected'));
            }
            if ($session->is_combined) {
                $this->refuse(__('A combined batch cannot be corrected, void it and enter it again'));
            }
            if ($quantity >= $previous) {
                $this->refuse(__('A correction can only lower the quantity, it was :quantity', ['quantity' => $previous]));
            }

            $task     = JobOrderItemTask::lockForUpdate()->find($session->job_order_item_task_id);
            $item     = $task->jobOrderItem;
            $jobOrder = $task->jobOrder;

            if (!in_array($jobOrder->state, JobOrderStateEnum::open()) || $item->quantity_received > 0) {
                $this->refuse(__('This job is already being received, its entries can no longer change'));
            }

            $packSize = $task->packSize();
            if ($packSize && $quantity != floor($quantity)) {
                $this->refuse(__('This step is counted in whole packs of :size, enter a whole number', ['size' => $packSize]));
            }

            $data       = $jobOrder->data ?? [];
            $authorised = collect($data['overproductions'] ?? [])->where('session_id', $session->id);
            $extra      = (float) $authorised->sum('quantity');
            $extraLeft  = round(max(0, $extra - ($previous - $quantity)), 3);

            $data['overproductions'] = collect($data['overproductions'] ?? [])
                ->map(fn (array $entry) => ($entry['session_id'] ?? null) == $session->id ? array_merge($entry, ['quantity' => $extraLeft]) : $entry)
                ->reject(fn (array $entry) => ($entry['session_id'] ?? null) == $session->id && $extraLeft <= 0)
                ->values()
                ->all();
            $data['corrections'][] = [
                'session_id'    => $session->id,
                'step'          => $task->manufactureTask->name,
                'artefact_code' => $item->artefact->code,
                'pack_size'     => $packSize,
                'from'          => $previous,
                'to'            => $quantity,
                'by'            => $this->correctedBy,
                'at'            => now()->toIso8601String(),
            ];
            $jobOrder->update(['data' => $data]);

            $session->quantity_made   = $quantity;
            $session->is_under_target = CloseManufactureTaskSession::make()->isUnderTarget($session);
            $session->save();

            if ($extra > $extraLeft) {
                $required         = round((float) $task->quantity_required - ($extra - $extraLeft), 3);
                $unitsPerArtefact = (float) ArtefactManufactureTask::where('artefact_id', $item->artefact_id)
                    ->where('manufacture_task_id', $task->manufacture_task_id)
                    ->value('units_per_artefact') ?: 1;

                $task->update(['quantity_required' => $required]);
                UpdateJobOrderItem::make()->action($item, ['quantity' => max(1, min((int) $item->quantity, (int) round($required / $unitsPerArtefact)))]);
            }

            CalculateJobOrderItemTaskQuantities::run($task->refresh());

            return CalculateManufactureTaskSessionPay::run($session);
        });
    }

    private function refuse(string $message): never
    {
        throw ValidationException::withMessages(['quantity_made' => $message]);
    }

    public function rules(): array
    {
        return [
            'quantity_made' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo([
            'org-supervisor.'.$this->organisation->id,
            "productions_operations.{$this->production->id}.orchestrate",
        ]);
    }

    public function action(ManufactureTaskSession $session, array $modelData, ?string $correctedBy = null): ManufactureTaskSession
    {
        $this->asAction    = true;
        $this->correctedBy = $correctedBy;
        $this->initialisationFromProduction($session->production, $modelData);

        return $this->handle($session, $this->validatedData);
    }

    public function asController(ManufactureTaskSession $manufactureTaskSession, ActionRequest $request): ManufactureTaskSession
    {
        $this->initialisationFromProduction($manufactureTaskSession->production, $request);
        $this->correctedBy = $request->user()->contact_name ?: $request->user()->username;

        return $this->handle($manufactureTaskSession, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
