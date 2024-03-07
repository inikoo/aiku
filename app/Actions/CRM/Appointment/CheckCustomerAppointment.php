<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:48:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Appointment;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Throwable;

class CheckCustomerAppointment
{
    use AsAction;
    use WithAttributes;
    use AsCommand;

    private bool $asAction = false;

    public function handle(array $modelData): array
    {
        return $modelData;
    }

    public function jsonResponse(array $modelData): array
    {
        return $modelData;
    }

    public function authorize(ActionRequest $request): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'exists:customers,email']
        ];
    }

    /**
     * @throws Throwable
     */
    public function asController(ActionRequest $request): array
    {
        $this->fillFromRequest($request);
        $request->validate();

        return $this->handle($request->validated());
    }
}
