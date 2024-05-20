<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:36:36 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User\UI;

use App\Http\Resources\HumanResources\ClockingMachineResource;
use App\Models\HumanResources\ClockingMachine;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreClockingMachineApiTokenFromQRCode
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;


    public function handle(ClockingMachine $clockingMachine, array $modelData): array
    {
        return [
            'token' => $clockingMachine->createToken(Arr::get($modelData, 'device_name', 'unknown-device'))->plainTextToken,
            'data'  => ClockingMachineResource::make($clockingMachine)
        ];
    }

    public function rules(): array
    {
        return [
            'qr_code'              => ['required', 'string', 'exists:clocking_machines,slug'],
            'device_name'          => ['required', 'string'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'id.required' => __('Invalid QR Code'),
        ];
    }


    public function asController(ActionRequest $request): array
    {
        $this->fillFromRequest($request);

        $validatedData = $this->validateAttributes();

        $clockingMachine = ClockingMachine::where('slug', $validatedData['qr_code'])->first();

        return $this->handle($clockingMachine, Arr::only($validatedData, ['device_name']));
    }
}
