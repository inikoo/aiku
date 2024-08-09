<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 12:17:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\Production;

use App\Actions\Manufacturing\Production\Hydrators\ProductionHydrateUniversalSearch;
use App\Actions\OrgAction;

use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateProductions;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProductions;
use App\Actions\SysAdmin\Organisation\SeedJobPositions;
use App\Actions\SysAdmin\User\UserAddRoles;
use App\Enums\Manufacturing\Production\ProductionStateEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Role;
use App\Rules\IUnique;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreProduction extends OrgAction
{
    public function handle(Organisation $organisation, $modelData): Production
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        /** @var Production $production */
        $production = $organisation->productions()->create($modelData);
        $production->stats()->create();

        SeedProductionPermissions::run($production);

        $orgAdmins = $organisation->group->users()->with('roles')->get()->filter(
            fn ($user) => $user->roles->where('name', "org-admin-$organisation->id")->toArray()
        );
        foreach ($orgAdmins as $orgAdmin) {
            UserAddRoles::run($orgAdmin, [
                Role::where('name', RolesEnum::getRoleName(RolesEnum::MANUFACTURING_ADMIN->value, $production))->first()
            ]);
        }

        GroupHydrateProductions::dispatch($organisation->group);
        OrganisationHydrateProductions::run($organisation);
        ProductionHydrateUniversalSearch::dispatch($production);
        SeedJobPositions::run($organisation);


        return $production;
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
            'code'       => [
                'required',
                'max:12',
                'alpha_dash',
                new IUnique(
                    table: 'productions',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->organisation->group_id],
                    ]
                ),
            ],
            'name'       => ['required', 'max:250', 'string'],
            'state'      => ['sometimes', Rule::enum(ProductionStateEnum::class)],
            'source_id'  => ['sometimes', 'string'],
            'created_at' => ['sometimes', 'date'],
        ];
    }

    public function action(Organisation $organisation, array $modelData): Production
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $this->validatedData);
    }


    public function asController(Organisation $organisation, ActionRequest $request): Production
    {
        $this->initialisation($organisation, $request);

        return $this->handle($organisation, $this->validatedData);
    }


    public function htmlResponse(Production $production): RedirectResponse
    {
        return Redirect::route('grp.org.productions.index');
    }

    public string $commandSignature = 'production:create {organisation : organisation slug} {code} {name} {--source_id=} {--state=} {--created_at=}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $organisation = Organisation::where('slug', $command->argument('organisation'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }
        $this->organisation = $organisation;
        setPermissionsTeamId($organisation->group->id);


        $modelData=[
            'code' => $command->argument('code'),
            'name' => $command->argument('name'),
        ];

        if($command->option('state')) {
            $modelData['state']=$command->option('state');
        }

        if($command->option('source_id')) {
            $modelData['source_id']=$command->option('source_id');
        }

        if($command->option('created_at')) {
            $modelData['created_at']=$command->option('created_at');
        }

        $this->setRawAttributes($modelData);

        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $production = $this->make()->action($organisation, $validatedData);

        $command->info("Production $production->code created successfully 🎉");

        return 0;
    }

}
