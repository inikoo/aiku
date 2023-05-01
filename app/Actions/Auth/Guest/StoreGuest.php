<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest;

use App\Enums\Auth\GuestTypeEnum;
use App\Models\Auth\Guest;
use App\Models\Tenancy\Tenant;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreGuest
{
    use AsAction;
    use WithAttributes;

    private bool $trusted = false;

    public function handle(array $modelData): Guest
    {
        return Guest::create($modelData);
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
            'name'                     => ['required', 'string', 'max:255'],
            'email'                    => ['nullable', 'email'],
            'phone'                    => ['nullable', 'phone:INTERNATIONAL'],
            'identity_document_number' => ['nullable', 'string'],
            'identity_document_type'   => ['nullable', 'string'],
            'type'                     => ['required', Rule::in(GuestTypeEnum::values())],

        ];
    }


    public function action(array $objectData): Guest
    {
        $this->trusted = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }

    public string $commandSignature = 'create:guest {tenant : tenant slug} {name} {type : Guest type contractor|external_employee|external_administrator} {--e|email=} {--t|phone=}  {--identity_document_number=} {--identity_document_type=}';

    public function asCommand(Command $command): int
    {
        $this->trusted = true;
        try {
            $tenant = Tenant::where('slug', $command->argument('tenant'))->firstOrFail();
        } catch (Exception) {
            $command->error("Tenant {$command->argument('tenant')} not found");

            return 1;
        }
        $tenant->makeCurrent();

        $this->fill([
            'type'                     => $command->argument('type'),
            'name'                     => $command->argument('name'),
            'email'                    => $command->option('email'),
            'phone'                    => $command->option('phone'),
            'identity_document_number' => $command->option('identity_document_number'),
            'identity_document_type'   => $command->option('identity_document_type'),
        ]);


        $validatedData = $this->validateAttributes();


        $guest = $this->handle($validatedData);
        $command->info("Guest <fg=yellow>$guest->slug</> created in <fg=yellow>$tenant->slug</> 👍");


        return 0;
    }

}
