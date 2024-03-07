<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Appointment;

use App\Enums\CRM\Appointment\AppointmentEventEnum;
use App\Enums\CRM\Appointment\AppointmentTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Market\Shop;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Throwable;

class StoreAppointment
{
    use AsAction;
    use WithAttributes;
    use AsCommand;

    private bool $asAction = false;

    public Customer|Shop $parent;

    /**
     * @throws \Exception
     */
    public function handle(Customer|Shop $parent, array $modelData): Model
    {
        $this->parent = $parent;

        if(class_basename($parent) == 'Shop') {
            data_set($modelData, 'customer_id', $modelData['customer_id']);
        } elseif(class_basename($parent) == 'Customer') {
            data_set($modelData, 'shop_id', $parent->shop_id);
            data_set($modelData, 'name', $parent->name);
        }

        /** @var \App\Models\CRM\Appointment $appointment */
        $appointment = $parent->appointment()->create($modelData);

        match($appointment->event) {
            AppointmentEventEnum::CALLBACK  => CreateMeetingUsingZoom::run($appointment),
            AppointmentEventEnum::IN_PERSON => $appointment->update(['event_address' => 'https://maps.app.goo.gl/Gr6RQbgkx2gkXuae7']),
            default                         => throw new Exception(__('Invalid appointment event'))
        };

        return $appointment;
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function htmlResponse(): RedirectResponse
    {
        return match (class_basename($this->parent)) {
            'Shop' => redirect()->route('org.crm.shop.appointments.index', [
                'shop' => $this->parent
            ]),
            default => back(),
        };
    }

    public function rules(): array
    {
        return [
            'customer_id'              => ['sometimes'],
            'name'                     => ['sometimes', 'string'],
            'schedule_at'              => ['required'],
            'description'              => ['nullable', 'string', 'max:255'],
            'type'                     => ['required', Rule::in(AppointmentTypeEnum::values())],
            'event'                    => ['required', Rule::in(AppointmentEventEnum::values())],
            'event_address'            => ['required', 'string']
        ];
    }

    /**
     * @throws Throwable
     */
    public function asController(Customer $customer, ActionRequest $request): Model
    {
        $this->fillFromRequest($request);
        $request->validate();

        return $this->handle($customer, $request->validated());
    }

    public function inShop(Shop $shop, ActionRequest $request): Model
    {
        $this->fillFromRequest($request);
        $request->validate();

        return $this->handle($shop, $request->validated());
    }

    public function inCustomer(ActionRequest $request): Model
    {
        $this->fillFromRequest($request);
        $request->validate();

        $customer = $request->user('customer')->customerUsers()->first()->customer;

        return $this->handle($customer, $request->validated());
    }

    public string $commandSignature = 'appointment:book {shop} {hour} {minute}';

    /**
     * @throws \Throwable
     */
    public function asCommand(Command $command): int
    {
        $shop   = $command->argument('shop');
        $hour   = $command->argument('hour');
        $minute = $command->argument('minute');

        try {
            $shop = Shop::where('slug', $shop)->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $this->setRawAttributes([
            'name'                 => fake()->name,
            'schedule_at'          => now()->setHours($hour)->setMinutes($minute),
            'type'                 => AppointmentTypeEnum::LEAD->value,
            'event'                => AppointmentEventEnum::IN_PERSON->value,
            'event_address'        => fake()->address
        ]);

        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $this->handle($shop->customers()->first(), $validatedData);

        $command->info("Appointment created successfully 🎉");

        return 0;
    }
}
