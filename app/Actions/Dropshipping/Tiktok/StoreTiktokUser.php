<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jul 2024 10:16:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Tiktok;

use App\Actions\CRM\Customer\AttachCustomerToPlatform;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\Platform;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTiktokUser extends RetinaAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(Customer $customer, array $modelData)
    {
        data_set($modelData, 'group_id', $customer->group_id);
        data_set($modelData, 'organisation_id', $customer->organisation_id);

        $platform = Platform::where('type', PlatformTypeEnum::TIKTOK->value)->first();
        AttachCustomerToPlatform::make()->action($customer, $platform, []);

        return $customer->tiktokUser()->create($modelData);
    }

    public function rules(): array
    {
        return [
            'tiktok_id' => ['required', 'string'],
            'name' => ['required', 'string'],
            'username' => ['required', 'string'],
            'access_token' => ['required', 'string'],
            'access_token_expire_in' => ['required'],
            'refresh_token' => ['required', 'string'],
            'refresh_token_expire_in' => ['required']
        ];
    }

    public function action(Customer $customer, array $modelData): void
    {
        $this->initialisationActions($customer, $modelData);

        $this->handle($customer, $this->validatedData);
    }
}
