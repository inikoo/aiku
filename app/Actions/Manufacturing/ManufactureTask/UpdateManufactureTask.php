<?php

namespace App\Actions\Manufacturing\ManufactureTask;

use App\Actions\Manufacturing\ManufactureTask\Hydrators\ManufactureTaskHydrateUniversalSearch;
use App\Actions\Manufacturing\Production\Hydrators\ProductionHydrateManufactureTasks;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateManufactureTasks;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateManufactureTasks;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardAllowanceTypeEnum;
use App\Enums\Manufacturing\ManufactureTask\ManufactureTaskOperativeRewardTermsEnum;
use App\Models\Manufacturing\ManufactureTask;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateManufactureTask extends OrgAction
{
    use WithActionUpdate;

    private ManufactureTask $manufactureTask;

    public function handle(ManufactureTask $manufactureTask, array $modelData): ManufactureTask
    {
        $manufactureTask = $this->update($manufactureTask, $modelData);
        if ($manufactureTask->wasChanged('operative_reward_terms') || $manufactureTask->wasChanged('operative_reward_allowance_type')) {
            GroupHydrateManufactureTasks::dispatch($manufactureTask->group);
            OrganisationHydrateManufactureTasks::dispatch($manufactureTask->organisation);
            ProductionHydrateManufactureTasks::dispatch($manufactureTask->production);
        }
        ManufactureTaskHydrateUniversalSearch::dispatch($manufactureTask);
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
            'code'        => [
                'sometimes',
                'alpha_dash',
                'max:64',
                new IUnique(
                    table: 'manufacture_tasks',
                    extraConditions: [
                        [
                            'column' => 'organisation_id',
                            'value'  => $this->organisation->id,
                        ],
                        [
                            'column'    => 'id',
                            'value'     => $this->manufactureTask->id,
                            'operation' => '!='
                        ]

                    ]
                ),
            ],
            'name'                              => ['sometimes', 'string', 'max:255'],
            'task_materials_cost'               => ['sometimes', 'numeric', 'min:0'],
            'task_energy_cost'                  => ['sometimes', 'numeric', 'min:0'],
            'task_other_cost'                   => ['sometimes', 'numeric', 'min:0'],
            'task_work_cost'                    => ['sometimes', 'numeric', 'min:0'],
            'task_lower_target'                 => ['sometimes', 'numeric', 'min:0'],
            'task_upper_target'                 => ['sometimes', 'numeric', 'min:0'],
            'operative_reward_terms'            => ['sometimes', Rule::enum(ManufactureTaskOperativeRewardTermsEnum::class)],
            'operative_reward_allowance_type'   => ['sometimes', Rule::enum(ManufactureTaskOperativeRewardAllowanceTypeEnum::class)],
            'operative_reward_amount'           => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function asController(ManufactureTask $manufactureTask, ActionRequest $request): ManufactureTask
    {
        $this->manufactureTask = $manufactureTask;
        $this->initialisation($manufactureTask->organisation, $request);

        return $this->handle(
            manufactureTask: $manufactureTask,
            modelData: $this->validatedData
        );
    }

    public function action(ManufactureTask $manufactureTask, $modelData): ManufactureTask
    {
        $this->asAction = true;
        $this->manufactureTask = $manufactureTask;
        $this->initialisation($manufactureTask->organisation, $modelData);

        return $this->handle(
            manufactureTask: $manufactureTask,
            modelData: $this->validatedData
        );
    }
}
