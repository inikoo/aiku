<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;

use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateGuests;
use App\Actions\SysAdmin\Guest\Hydrators\GuestHydrateUniversalSearch;
use App\Actions\SysAdmin\User\StoreUser;
use App\Actions\SysAdmin\User\UserAddRoles;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\Inventory\Warehouse;
use App\Models\Catalogue\Shop;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Role;
use App\Rules\AlphaDashDot;
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
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreGuest
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    private Group $group;

    private bool $validatePhone = false;

    public function handle(Group $group, array $modelData): Guest
    {
        $rolesNames = Arr::get($modelData, 'roles', []);
        Arr::forget($modelData, 'roles');


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

        $user = StoreUser::make()->action(
            $guest,
            [
                'username'       => Arr::get($modelData, 'username'),
                'password'       => Arr::get($modelData, 'password'),
                'contact_name'   => $guest->contact_name,
                'email'          => $guest->email,
                'reset_password' => Arr::get($modelData, 'reset_password', false),
            ]
        );

        $roles = [];
        foreach ($rolesNames as $roleName) {
            $role    = Role::where('name', $roleName)->where('group_id', $group->id)->first();
            $roles[] = $role->name;

            if ($role->name === RolesEnum::SUPER_ADMIN->value) {
                foreach (Organisation::all() as $organisation) {
                    UserAddRoles::run($user, [
                        Role::where('name', RolesEnum::getRoleName(RolesEnum::ORG_SHOP_ADMIN->value, $organisation))->first()
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
            $phoneValidation[] = 'phone:AUTO';
        }


        return [
            'alias'          => ['required', 'iunique:guests', 'string', 'max:12', Rule::notIn(['export', 'create'])],
            'username'       => ['required', 'required', new AlphaDashDot(), 'iunique:users', Rule::notIn(['export', 'create'])],
            'company_name'   => ['nullable', 'string', 'max:255'],
            'contact_name'   => ['required', 'string', 'max:255'],
            'phone'          => $phoneValidation,
            'email'          => ['sometimes', 'nullable', 'email'],
            'roles.*'        => [
                Rule::exists('roles', 'name')
                    ->where('group_id', $this->group->id)
            ],
            'password'       => ['sometimes', 'required', 'max:255', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'reset_password' => ['sometimes', 'boolean'],
            'source_id'      => ['sometimes', 'string'],
        ];
    }


    public function asController(ActionRequest $request): Guest
    {
        $this->fillFromRequest($request);
        $this->group = app('group');

        return $this->handle(app('group'), $this->validateAttributes());
    }


    public function action(Group $group, array $modelData): Guest
    {
        $this->asAction = true;
        $this->group    = $group;

        $this->setRawAttributes($modelData);

        return $this->handle($group, $this->validateAttributes());
    }

    public string $commandSignature = 'guest:create {group : group slug} {name} {username}
     {--roles=*}
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
        $fields = [
            'roles'        => $command->option('roles'),
            'contact_name' => $command->argument('name'),
            'email'        => $command->option('email'),
            'phone'        => $command->option('phone'),
            'username'     => $command->argument('username'),
            'password'     => $command->option('password') ?? (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true))
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
