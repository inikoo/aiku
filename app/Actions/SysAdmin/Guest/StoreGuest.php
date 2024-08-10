<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;

use App\Actions\GrpAction;
use App\Actions\HumanResources\JobPosition\SyncGuestJobPositions;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateGuests;
use App\Actions\SysAdmin\Guest\Hydrators\GuestHydrateUniversalSearch;
use App\Actions\SysAdmin\User\StoreUser;
use App\Actions\SysAdmin\User\UserAddRoles;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\HumanResources\JobPosition;
use App\Models\Inventory\Warehouse;
use App\Models\Catalogue\Shop;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Role;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use App\Rules\Phone;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class StoreGuest extends GrpAction
{
    private bool $validatePhone = false;

    public function handle(Group $group, array $modelData): Guest
    {
        $positions = Arr::get($modelData, 'positions', []);
        data_forget($modelData, 'positions');


        /** @var Guest $guest */
        $guest = $group->guests()->create(
            Arr::except($modelData, [
                'username',
                'password',
                'reset_password'
            ])
        );
        $guest->stats()->create();

        GuestHydrateUniversalSearch::dispatch($guest);

        StoreUser::make()->action(
            $guest,
            [
                'username'       => Arr::get($modelData, 'username'),
                'password'       => Arr::get($modelData, 'password'),
                'contact_name'   => $guest->contact_name,
                'email'          => $guest->email,
                'reset_password' => Arr::get($modelData, 'reset_password', false),
            ]
        );

        $jobPositions = [];
        foreach ($positions as $positionData) {
            $jobPosition                    = JobPosition::firstWhere('slug', $positionData['slug']);
            $jobPositions[$jobPosition->id] = $positionData['scopes'];
        }


        SyncGuestJobPositions::run($guest, $jobPositions);


        /*

        $roles = [];
        foreach ($rolesNames as $roleName) {
            $role    = Role::where('name', $roleName)->where('group_id', $group->id)->first();
            $roles[] = $role->name;

            if ($role->name === RolesEnum::GROUP_ADMIN->value) {
                foreach (Organisation::all() as $organisation) {
                    UserAddRoles::run($user, [
                        Role::where('name', RolesEnum::getRoleName(RolesEnum::ORG_ADMIN->value, $organisation))->first()
                    ]);
                }
                foreach (Shop::all() as $shop) {
                    if ($shop->type == ShopTypeEnum::FULFILMENT) {
                        UserAddRoles::run($user, [
                            Role::where('name', RolesEnum::getRoleName(RolesEnum::FULFILMENT_WAREHOUSE_SUPERVISOR->value, $shop->fulfilment))->first()
                        ]);
                        UserAddRoles::run($user, [
                            Role::where('name', RolesEnum::getRoleName(RolesEnum::FULFILMENT_SHOP_SUPERVISOR->value, $shop->fulfilment))->first()
                        ]);
                    } else {
                        UserAddRoles::run($user, [
                            Role::where('name', RolesEnum::getRoleName(RolesEnum::SHOP_ADMIN->value, $shop))->first()
                        ]);
                    }
                }
                foreach (Warehouse::all() as $warehouse) {
                    UserAddRoles::run($user, [
                        Role::where('name', RolesEnum::getRoleName(RolesEnum::WAREHOUSE_ADMIN->value, $warehouse))->first()
                    ]);
                }

                foreach (Production::all() as $production) {
                    UserAddRoles::run($user, [
                        Role::where('name', RolesEnum::getRoleName(RolesEnum::MANUFACTURING_ADMIN->value, $production))->first()
                    ]);
                }
            }
        }
        UserAddRoles::run($user, $roles);
        */

        GroupHydrateGuests::dispatch($group);

        return $guest;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }

    public function prepareForValidation(): void
    {
        if (!$this->has('alias')) {
            $this->set('alias', $this->get('username'));
        }
        if ($this->get('phone')) {
            $this->set('phone', preg_replace('/[^0-9+]/', '', $this->get('phone')));
        }
    }

    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if ($validator->errors()->has('alias')) {
            $validator->errors()->add('username', $validator->errors()->first('alias'));
        }
    }

    public function rules(): array
    {
        $phoneValidation = ['sometimes', 'nullable'];

        if ($this->validatePhone) {
            $phoneValidation[] = new Phone();
        }


        return [
            'alias'        => [
                'required',
                'string',
                'max:12',
                Rule::notIn(['export', 'create']),
                new IUnique(table: 'guests'),

            ],
            'username'     => [
                'required',
                'string',
                new AlphaDashDot(),
                Rule::notIn(['export', 'create']),
                new IUnique(table: 'users'),
            ],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'phone'        => $phoneValidation,
            'email'        => ['sometimes', 'nullable', 'email'],
            'positions'    => ['sometimes', 'array'],

            'positions.*.slug'   => ['sometimes', 'string', Rule::exists('job_positions', 'slug')->where('group_id', $this->group->id)],
            'positions.*.scopes' => ['sometimes', 'array'],

            'positions.*.scopes.organisations.slug.*' => ['sometimes', Rule::exists('organisations', 'slug')->where('group_id', $this->group->id)],
            'positions.*.scopes.warehouses.slug.*'    => ['sometimes', Rule::exists('warehouses', 'slug')->where('group_id', $this->group->id)],
            'positions.*.scopes.fulfilments.slug.*'   => ['sometimes', Rule::exists('fulfilments', 'slug')->where('group_id', $this->group->id)],
            'positions.*.scopes.shops.slug.*'         => ['sometimes', Rule::exists('shops', 'slug')->where('group_id', $this->group->id)],
            'password'                                => ['sometimes', 'required', 'max:255', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'reset_password'                          => ['sometimes', 'boolean'],
            'source_id'                               => ['sometimes', 'string'],
        ];
    }


    public function asController(ActionRequest $request): Guest
    {
        $this->initialisation(app('group'), $request);

        return $this->handle($this->group, $this->validatedData);
    }


    public function action(Group $group, array $modelData): Guest
    {
        $this->asAction = true;
        $this->initialisation($group, $modelData);


        return $this->handle($group, $this->validatedData);
    }

    public string $commandSignature = 'guest:create {group : group slug} {name} {username}
     {--positions=}
     {--P|password=} {--e|email=} {--t|phone=} {--identity_document_number=} {--identity_document_type=}';


    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $group       = Group::where('slug', $command->argument('group'))->firstOrFail();
            $this->group = $group;
            app()->instance('group', $group);
            setPermissionsTeamId($group->id);
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $positions = json_decode($command->option('positions'), true);


        $fields = [
            'positions'     => $positions,
            'contact_name'  => $command->argument('name'),
            'email'         => $command->option('email'),
            'phone'         => $command->option('phone'),
            'username'      => $command->argument('username'),
            'password'      => $command->option('password') ?? (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true))
        ];

        $this->fill($fields);

        $guest = $this->handle($group, $this->validateAttributes());


        $command->info("Guest <fg=yellow>$guest->slug</> created 👍");


        return 0;
    }

    public function htmlResponse(Guest $guest): RedirectResponse
    {
        return Redirect::route('grp.sysadmin.guests.show', $guest->slug);
    }

}
