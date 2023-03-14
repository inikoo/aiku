<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 09:31:51 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Family;

use App\Actions\Marketing\Family\Hydrators\FamilyHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Marketing\FamilyResource;
use App\Models\Marketing\Family;
use Lorisleiva\Actions\ActionRequest;

class UpdateFamily
{
    use WithActionUpdate;

    public function handle(Family $family, array $modelData): Family
    {
        $family = $this->update($family, $modelData, ['data']);
        FamilyHydrateUniversalSearch::dispatch($family);
        return $family;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.products.edit");
    }
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required'],
            'name' => ['sometimes', 'required'],
        ];
    }


    public function asController(Family $family, ActionRequest $request): Family
    {
        $request->validate();
        return $this->handle($family, $request->all());
    }


    public function jsonResponse(Family $family): FamilyResource
    {
        return new FamilyResource($family);
    }
}
