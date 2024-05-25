<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Workplace;

use App\Actions\HumanResources\Workplace\Hydrators\WorkplaceHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWorkplaces;
use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;
use App\Models\Helpers\Address;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Enum;
use Lorisleiva\Actions\ActionRequest;

class StoreWorkplace extends OrgAction
{
    public function handle(Organisation $organisation, array $modelData): Workplace
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        $addressData = Arr::get($modelData, 'address');
        Arr::forget($modelData, 'address');
        /** @var Workplace $workplace */
        $workplace = $organisation->workplaces()->create($modelData);

        data_set($addressData, 'owner_type', 'Workplace');
        data_set($addressData, 'owner_id', $workplace->id);
        $address=Address::create($addressData);
        $workplace->address()->associate($address);
        $workplace->location = $workplace->address->getLocation();

        $workplace->save();
        $workplace->stats()->create();
        OrganisationHydrateWorkplaces::run($organisation);
        WorkplaceHydrateUniversalSearch::dispatch($workplace);

        return $workplace;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'name'        => [
                'required',
                'max:255',
                new IUnique(
                    table: 'workplaces',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                    ]
                ),
            ],
            'type'        => ['required', new Enum(WorkplaceTypeEnum::class)],
            'address'     => ['sometimes', 'required', new ValidAddress()],
            'settings'    => ['sometimes', 'array'],
            'timezone_id' => ['sometimes', 'nullable', 'exists:timezones,id']
        ];
    }

    public function asController(Organisation $organisation, ActionRequest $request): Workplace
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, $this->validatedData);
    }

    public function htmlResponse(Workplace $workplace): RedirectResponse
    {
        return Redirect::route('grp.org.hr.workplaces.show', [
            'organisation' => $workplace->organisation->slug,
            'workplace'    => $workplace->slug
        ]);
    }

    public function action(Organisation $organisation, array $modelData): Workplace
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $this->validatedData);
    }

    public string $commandSignature = 'workplace:create {organisation} {name} {type}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $organisation = Organisation::where('slug', $command->argument('organisation'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        try {
            $this->initialisation($organisation, [
                'address' => [
                    'country_id' => $organisation->country_id
                ],
                'name'    => $command->argument('name'),
                'type'    => $command->argument('type'),

            ]);
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $workplace = $this->handle(organisation: $organisation, modelData: $this->validatedData);

        $command->info("Workplace $workplace->slug created successfully 🎉");

        return 0;
    }
}
