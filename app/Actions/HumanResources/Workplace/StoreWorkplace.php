<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Workplace;

use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\HumanResources\Workplace\Hydrators\WorkplaceHydrateUniversalSearch;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateWorkplaces;
use App\Enums\HumanResources\Workplace\WorkplaceTypeEnum;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use App\Rules\ValidAddress;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Enum;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreWorkplace
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;


    public function handle(Organisation $organisation, array $modelData): Workplace
    {
        $addressData = Arr::get($modelData, 'address');
        Arr::forget($modelData, 'address');
        /** @var Workplace $workplace */
        $workplace = $organisation->workplaces()->create($modelData);
        StoreAddressAttachToModel::run($workplace, $addressData, ['scope' => 'contact']);

        $workplace->location = $workplace->getLocation();
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

        return $request->user()->hasPermissionTo("hr.edit");
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'max:255'],
            'type'    => ['required', new Enum(WorkplaceTypeEnum::class)],
            'address' => ['required', new ValidAddress()]
        ];
    }

    public function asController(Organisation $organisation, ActionRequest $request): Workplace
    {
        $request->validate();
        $validatedData = $request->validated();

        return $this->handle($organisation, $validatedData);
    }

    public function htmlResponse(Workplace $workplace): RedirectResponse
    {
        return Redirect::route('org.hr.workplaces.show', $workplace->slug);
    }

    public function action(Organisation $organisation, array $modelData): Workplace
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($organisation, $validatedData);
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


        $this->setRawAttributes([
            'address' => [
                'country_id' => $organisation->country_id
            ],
            'name'    => $command->argument('name'),
            'type'    => $command->argument('type'),

        ]);

        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $workplace = $this->handle(organisation: $organisation, modelData: $validatedData);

        $command->info("Workplace $workplace->slug created successfully 🎉");

        return 0;
    }
}
