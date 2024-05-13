<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Appointment;

use App\Models\CRM\Appointment;
use App\Models\HumanResources\Employee;
use App\Models\Catalogue\Shop;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Throwable;

class GetBookedScheduleAppointment
{
    use AsAction;
    use WithAttributes;


    /**
     * @throws Throwable
     */
    public function handle(array $modelData): array
    {
        $dt                 = Carbon::createFromDate($modelData['year'], $modelData['month']);
        $bookedSchedules    = [];
        $availableSchedules = [];
        $availableTimes     = ['02:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00'];

        $endOfMonth = $dt->copy()->endOfMonth();
        $currentDay = now();

        $daysInMonth = $currentDay->diffInDays($endOfMonth);

        for ($i = 0; $i <= $daysInMonth; $i++) {
            $date = now()->addDays($i);
            $date = $date->format('Y-m-d');

            $employees = Employee::whereHas('jobPositions', function ($query) {
                return $query->where('code', 'cus-c');
            })->pluck('id');

            $appointment = Appointment::whereDate('schedule_at', $date)
                ->pluck('schedule_at');

            if($employees->count() != 0) {
                $bookedSchedules[$date] = $availableTimes;
            } elseif(count($appointment) > 0) {
                $bookedSchedules[$date] = $appointment->map(function ($item) {
                    return Carbon::parse($item)->format('H:i');
                })->toArray();
            }

            $availableSchedules[$date] = array_diff($availableTimes, Arr::get($bookedSchedules, $date, []));
        }

        return [
            'availableSchedules' => $availableSchedules
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year'  => ['required', 'string'],
            'month' => ['required', 'string']
        ];
    }

    /**
     * @throws Throwable
     */
    public function asController(ActionRequest $request): array
    {
        $request->validate();

        return $this->handle($request->validated());
    }

    public string $commandSignature = 'shop:new-customer {shop} {email} {--N|contact_name=} {--C|company=} {--P|password=}';

    /**
     * @throws \Throwable
     */
    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $shop = Shop::where('slug', $command->argument('shop'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }


        $this->setRawAttributes([
            'contact_name' => $command->option('contact_name'),
            'company_name' => $command->option('company'),
            'email'        => $command->argument('email'),
        ]);

        try {
            $validatedData = $this->validateAttributes();
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $customer = $this->handle($shop, $validatedData);

        $command->info("Customer $customer->slug created successfully 🎉");

        return 0;
    }
}
