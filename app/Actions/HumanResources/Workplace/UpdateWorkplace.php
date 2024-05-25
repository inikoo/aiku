<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Workplace;

use App\Actions\HumanResources\Workplace\Hydrators\WorkplaceHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWorkplaces;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithModelAddressActions;
use App\Http\Resources\HumanResources\WorkplaceResource;
use App\Models\Helpers\Address;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateWorkplace extends OrgAction
{
    use WithActionUpdate;
    use WithModelAddressActions;

    private Workplace $workplace;

    public function handle(Workplace $workplace, array $modelData): Workplace
    {
        $addressData = Arr::get($modelData, 'address');
        Arr::forget($modelData, 'address');

        $workplace = $this->update($workplace, $modelData, ['data']);

        if($addressData) {
            if($workplace->address) {
                $workplace->address()->update($addressData);
            } else {
                data_set($addressData, 'owner_type', 'Workplace');
                data_set($addressData, 'owner_id', $workplace->id);
                $address = Address::create($addressData);
                $workplace->address()->associate($address);
            }
            $workplace->location = $workplace->address->getLocation();
            $workplace->save();
        }

        if ($workplace->wasChanged('type')) {
            OrganisationHydrateWorkplaces::run($workplace->organisation);
        }

        WorkplaceHydrateUniversalSearch::dispatch($workplace);

        return $workplace;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'name'    => [
                'sometimes',
                'required',
                'max:255',
                new IUnique(
                    table: 'workplaces',
                    extraConditions: [
                        [
                            'column' => 'group_id',
                            'value'  => $this->organisation->group_id,

                        ],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->workplace->id
                        ],
                    ]
                ),
            ],
            'type'    => ['sometimes', 'required'],
            'address' => ['sometimes', 'required', new ValidAddress()]
        ];
    }

    public function asController(Organisation $organisation, Workplace $workplace, ActionRequest $request): Workplace
    {
        $this->initialisation($organisation, $request);
        $this->workplace = $workplace;


        return $this->handle(workplace: $workplace, modelData: $this->validatedData);
    }

    public function jsonResponse(Workplace $workplace): WorkplaceResource
    {
        return new WorkplaceResource($workplace);
    }
}
