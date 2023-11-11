<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest;

use App\Actions\Auth\GroupUser\StoreGroupUser;
use App\Actions\Auth\Guest\Hydrators\GuestHydrateUniversalSearch;
use App\Actions\Auth\User\StoreUser;
use App\Actions\Organisation\Organisation\Hydrators\OrganisationHydrateGuests;
use App\Enums\Auth\Guest\GuestTypeEnum;
use App\Models\Auth\GroupUser;
use App\Models\Auth\Guest;
use App\Models\Organisation\Organisation;
use App\Rules\AlphaDashDot;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreGuest
{
    use AsAction;
    use WithAttributes;

    private bool $trusted = false;

    public function handle(Organisation $organisation, array $modelData): Guest
    {
        $guest = Guest::create($modelData);
        OrganisationHydrateGuests::dispatch($organisation);
        GuestHydrateUniversalSearch::dispatch($guest);
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
            'type'         => ['required', Rule::in(GuestTypeEnum::values())],
            'username'     => ['sometimes','nullable', new AlphaDashDot(), 'unique:App\Models\SysAdmin\SysUser,username', Rule::notIn(['export', 'create'])],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'phone'        => ['nullable', 'phone:AUTO'],
            'email'        => ['nullable', 'email'],


        ];
    }

    public function asController(Organisation $organisation, ActionRequest $request): Guest
    {
        $request->validate();

        $modelData = $request->validated();

        $guest = $this->handle($organisation, Arr::except($modelData, ['username']));

        $groupUser = GroupUser::where('username', Arr::get($modelData, 'username'))->first();
        if (!$groupUser) {
            $groupUser = StoreGroupUser::run(
                array_merge(
                    [
                        'username' => Arr::get($modelData, 'username'),
                        'password' => (app()->isLocal() ? 'hello' : wordwrap(Str::random(), 4, '-', true))

                    ]
                )
            );
        }
        StoreUser::run(
            parent: $guest,
            groupUser: $groupUser,
        );

        return $guest;
    }

    public function inGroupUser(GroupUser $groupUser, ActionRequest $request): Guest
    {
        $request->validate();
        $modelData = $request->validated();
        $guest     = $this->handle($modelData);

        StoreUser::run(
            parent: $guest,
            groupUser: $groupUser,
        );

        return $guest;
    }


    public function action(Organisation $organisation, array $objectData): Guest
    {
        $this->trusted = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($organisation, $validatedData);
    }

    public string $commandSignature = 'create:guest {tenant : tenant slug} {name} {type : Guest type contractor|external_employee|external_administrator} {--e|email=} {--t|phone=}  {--identity_document_number=} {--identity_document_type=}';

    /**
     * @throws ModelNotFoundException
     */
    public function asCommand(Command $command): int
    {
        $this->trusted       = true;
        $organisation        = Organisation::where('slug', $command->argument('tenant'))->firstOrFail();
        $organisation->makeCurrent();

        $this->fill([
            'type'         => $command->argument('type'),
            'contact_name' => $command->argument('name'),
            'email'        => $command->option('email'),
            'phone'        => $command->option('phone'),
        ]);


        $validatedData = $this->validateAttributes();


        $guest = $this->handle($validatedData);
        $command->info("Guest <fg=yellow>$guest->slug</> created in <fg=yellow>$organisation->slug</> 👍");


        return 0;
    }

    public function htmlResponse(Guest $guest): RedirectResponse
    {
        return Redirect::route('sysadmin.guests.show', $guest->slug);
    }

}
