<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:43:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Leads\Prospect;

use App\Actions\Leads\Prospect\Hydrators\ProspectHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Lead\ProspectResource;
use App\Models\Leads\Prospect;
use Lorisleiva\Actions\ActionRequest;

class UpdateProspect
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(Prospect $prospect, array $modelData): Prospect
    {
        $prospect = $this->update($prospect, $modelData, ['data']);
        ProspectHydrateUniversalSearch::dispatch($prospect);

        return $prospect;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("shops.customers.edit");
    }

    public function rules(): array
    {
        return [
            'contact_name' => ['sometimes'],
            'company_name' => ['sometimes'],
            'phone'        => 'sometimes|phone:AUTO',
            'website'      => ['sometimes', 'nullable', 'active_url'],
            'email'        => ['sometimes', 'nullable', 'email'],
        ];
    }

    public function asController(Prospect $prospect, ActionRequest $request): Prospect
    {
        $request->validate();
        return $this->handle($prospect, $request->all());
    }

    public function action(Prospect $prospect, $objectData): Prospect
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($prospect, $validatedData);
    }

    public function jsonResponse(Prospect $prospect): ProspectResource
    {
        return new ProspectResource($prospect);
    }
}
