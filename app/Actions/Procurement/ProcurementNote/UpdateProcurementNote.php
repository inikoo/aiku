<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 02:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\ProcurementNote;

use App\Actions\OrgAction;
use App\Actions\Procurement\WithAgreedTermsAuthorisation;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteIncidentOutcomeEnum;
use App\Enums\Procurement\ProcurementNote\ProcurementNoteStateEnum;
use App\Models\Procurement\ProcurementNote;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateProcurementNote extends OrgAction
{
    use WithAgreedTermsAuthorisation;

    private ProcurementNote $procurementNote;

    public function handle(ProcurementNote $procurementNote, array $modelData): ProcurementNote
    {
        if ($state = Arr::get($modelData, 'state')) {
            $resolved                           = $state !== ProcurementNoteStateEnum::ACTIVE->value;
            $modelData['resolved_at']           = $resolved ? now() : null;
            $modelData['resolved_by_user_id']   = $resolved ? request()->user()?->id : null;
            if ($resolved) {
                $modelData['is_pinned'] = false;
            }
        }

        $procurementNote->update($modelData);

        return $procurementNote;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $this->procurementNote->author_user_id === $request->user()->id
            || $this->isProcurementSupervisor($request->user(), $this->group, $this->procurementNote->organisation_id);
    }

    public function rules(): array
    {
        return [
            'is_pinned'        => ['sometimes', 'boolean'],
            'state'            => ['sometimes', Rule::enum(ProcurementNoteStateEnum::class)],
            'incident_outcome' => ['sometimes', Rule::enum(ProcurementNoteIncidentOutcomeEnum::class)],
        ];
    }

    public function action(ProcurementNote $procurementNote, array $modelData): ProcurementNote
    {
        $this->asAction = true;
        $this->initialisationFromGroup($procurementNote->group, $modelData);

        return $this->handle($procurementNote, $this->validatedData);
    }

    public function update(ProcurementNote $procurementNote, ActionRequest $request): void
    {
        $this->procurementNote = $procurementNote;
        $this->initialisationFromGroup($procurementNote->group, $request);
        $this->handle($procurementNote, $this->validatedData);
    }
}
