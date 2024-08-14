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
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Guest;
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
        if (!$this->has('code')) {
            $this->set('code', $this->get('username'));
        }
        if ($this->get('phone')) {
            $this->set('phone', preg_replace('/[^0-9+]/', '', $this->get('phone')));
        }
    }

    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if ($validator->errors()->has('code')) {
            $validator->errors()->add('username', $validator->errors()->first('code'));
        }
    }

    public function rules(): array
    {
        $phoneValidation = ['sometimes', 'nullable'];

        if ($this->validatePhone) {
            $phoneValidation[] = new Phone();
        }


        return [
            'code'        => [
                'required',
                'string',
                'max:32',
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
