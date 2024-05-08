<?php

namespace App\Actions\Manufacturing\ManufactureTask;

use App\Actions\Manufacturing\Hydrators\GroupHydrateManufacture;
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

    public function handle(Organisation $organisation, $modelData): ManufactureTask
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        data_set($modelData, 'organisation_id', $organisation->organisation_id);

        /** @var ManufactureTask $manufactureTask */
        $manufactureTask = $organisation->manufactureTasks()->create($modelData);

        ManufactureTaskHydrateUniversalSearch::dispatch($manufactureTask);
        GroupHydrateManufactureTasks::dispatch($manufactureTask->group);
        OrganisationHydrateManufactureTasks::dispatch($manufactureTask->organisation);

        return $manufactureTask;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.productions.edit");
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
            'task_from'                         => ['required', 'date'],
            'task_to'                           => ['required', 'date'],
            'task_active'                       => ['required', 'boolean'],
            'task_lower_target'                 => ['required', 'numeric', 'min:0'],
            'task_upper_target'                 => ['required', 'numeric', 'min:0'],
            'operative_reward_terms'            => ['required', Rule::enum(ManufactureTaskOperativeRewardTermsEnum::class)],
            'operative_reward_allowance_type'   => ['required', Rule::enum(ManufactureTaskOperativeRewardAllowanceTypeEnum::class)],
            'operative_reward_amount'           => ['required', 'numeric', 'min:0'],
        ];
    }

    public function action(Organisation $organisation, array $modelData): ManufactureTask
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $this->validatedData);
    }

    public function asController(Organisation $organisation, ActionRequest $request): ManufactureTask
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, $this->validatedData);
    }
}
