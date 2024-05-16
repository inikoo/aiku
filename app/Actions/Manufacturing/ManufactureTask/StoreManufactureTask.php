<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 May 2024 10:36:02 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\ManufactureTask;

use App\Actions\Manufacturing\ManufactureTask\Hydrators\ManufactureTaskHydrateUniversalSearch;
use App\Actions\Manufacturing\Production\Hydrators\ProductionHydrateManufactureTasks;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateManufactureTasks;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateManufactureTasks;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardAllowanceTypeEnum;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardTermsEnum;
use App\Models\Manufacturing\ManufactureTask;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreManufactureTask extends OrgAction
{
    use AsAction;

    public function handle(Production $production, $modelData): ManufactureTask
    {
        data_set($modelData, 'group_id', $production->group_id);
        data_set($modelData, 'organisation_id', $production->organisation_id);

        /** @var ManufactureTask $manufactureTask */
        $manufactureTask = $production->manufactureTasks()->create($modelData);
        $manufactureTask->stats()->create();

        ManufactureTaskHydrateUniversalSearch::dispatch($manufactureTask);
        GroupHydrateManufactureTasks::dispatch($manufactureTask->group);
        OrganisationHydrateManufactureTasks::dispatch($manufactureTask->organisation);
        ProductionHydrateManufactureTasks::dispatch($production);

        return $manufactureTask;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("productions-view.{$this->organisation->id}");
    }

    public function rules(): array
    {
        return [
            'code'             => [
                'required',
                'alpha_dash',
                'max:64',
                new IUnique(
                    table: 'manufacture_tasks',
                    extraConditions: [
                        ['column' => 'organisation_id', 'value' => $this->organisation->id],
                    ]
                ),
            ],
            'name'                              => ['required', 'string', 'max:255'],
            'task_materials_cost'               => ['required', 'numeric', 'min:0'],
            'task_energy_cost'                  => ['required', 'numeric', 'min:0'],
            'task_other_cost'                   => ['required', 'numeric', 'min:0'],
            'task_work_cost'                    => ['required', 'numeric', 'min:0'],
            'task_lower_target'                 => ['required', 'numeric', 'min:0'],
            'task_upper_target'                 => ['required', 'numeric', 'min:0'],
            'operative_reward_terms'            => ['required', Rule::enum(ManufactureTaskOperativeRewardTermsEnum::class)],
            'operative_reward_allowance_type'   => ['required', Rule::enum(ManufactureTaskOperativeRewardAllowanceTypeEnum::class)],
            'operative_reward_amount'           => ['required', 'numeric', 'min:0'],
        ];
    }

    public function action(Production $production, array $modelData): ManufactureTask
    {
        $this->asAction       = true;
        $this->initialisationFromProduction($production, $modelData);

        return $this->handle($production, $this->validatedData);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): ManufactureTask
    {
        $this->initialisationFromProduction($production, $request);

        return $this->handle($production, $this->validatedData);
    }
}
