<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;

use App\Actions\HumanResources\SyncJobPosition;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateGuests;
use App\Actions\SysAdmin\Guest\Hydrators\GuestHydrateUniversalSearch;
use App\Actions\SysAdmin\User\StoreUser;
use App\Enums\Auth\Guest\GuestTypeEnum;
use App\Models\SysAdmin\Group;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use App\Rules\AlphaDashDot;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreGuest
{
    use AsAction;
    use WithAttributes;

    private bool $trusted = false;

    public function handle(Group $group, array $modelData): Guest
    {
        $positions = Arr::get($modelData, 'positions', []);
        Arr::forget($modelData, 'positions');


        /** @var \App\Models\SysAdmin\Guest $guest */
        $guest = $group->guests()->create(
            Arr::except($modelData, [
                'username',
                'password',
                'reset_password'
            ])
        );

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
        foreach ($positions as $position) {
            $jobPosition    = JobPosition::firstWhere('slug', $position);
            $jobPositions[] = $jobPosition->id;
        }
        SyncJobPosition::run($guest, $jobPositions);
        GroupHydrateGuests::dispatch($group);
        return $guest;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->trusted) {
            return true;
        }

        return $request->user()->hasPermissionTo("sysadmin.edit");
    }

    public function prepareForValidation(): void
    {
        if ($this->get('phone')) {
            $this->set('phone', preg_replace('/[^0-9+]/', '', $this->get('phone')));
        }
    }

    public function rules(): array
    {
        return [
            'type'           => ['required', Rule::in(GuestTypeEnum::values())],
            'alias'          => ['required', 'iunique:guests', 'string', 'max:12'],
            'username'       => ['required', 'required', new AlphaDashDot(), 'iunique:users'],
            'company_name'   => ['nullable', 'string', 'max:255'],
            'contact_name'   => ['required', 'string', 'max:255'],
            'phone'          => ['nullable', 'phone:AUTO'],
            'email'          => ['nullable', 'email'],
            'positions.*'    => ['exists:job_positions,slug'],
            'password'       => ['sometimes', 'required', 'max:255', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
            'reset_password' => ['sometimes', 'boolean']
        ];
    }

    public function asController(Group $group, ActionRequest $request): Guest
    {
        $request->validate();

        $modelData = $request->validated();

        $guest = $this->handle($group, Arr::except($modelData, ['username']));

        $user = User::where('username', Arr::get($modelData, 'username'))->first();
        if (!$user) {
            StoreUser::run(
                parent: $guest,
                modelData: array_merge(
                    [
                        'username' => Arr::get($modelData, 'username'),
                        'password' => (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true))

                    ]
                )
            );
        }


        return $guest;
    }


    public function action(Group $group, array $objectData): Guest
    {
        $this->trusted = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($group, $validatedData);
    }

    public string $commandSignature = 'guest:create {group : group slug} {name} {alias} {type : Guest type contractor|external_employee|external_administrator} {--P|password=} {--e|email=} {--t|phone=} {--identity_document_number=} {--identity_document_type=}';


    public function asCommand(Command $command): int
    {
        $this->trusted = true;

        try {
            $group = Group::where('code', $command->argument('group'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }
        $fields = [
            'type'         => $command->argument('type'),
            'contact_name' => $command->argument('name'),
            'email'        => $command->option('email'),
            'phone'        => $command->option('phone'),
            'alias'        => $command->argument('alias'),
            'username'     => $command->argument('alias'),
            'password'     => $command->option('password') ?? (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true))
        ];

        if ($command->argument('type') == GuestTypeEnum::EXTERNAL_ADMINISTRATOR->value) {
            $fields['positions'] = ['admin'];
        }


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
