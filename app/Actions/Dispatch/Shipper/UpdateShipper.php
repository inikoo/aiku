<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipper;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Dispatch\Shipper;

class UpdateShipper
{
    use WithActionUpdate;

    public function handle(Shipper $shipper, array $modelData): Shipper
    {
        return $this->update($shipper, $modelData, ['data']);
    }

    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:shippers', 'between:2,9', 'alpha'],
            'name'         => ['required', 'max:250', 'string'],
            'api_shipper'  => ['sometimes', 'required'],
            'contact_name' => ['sometimes', 'required'],
            'company_name' => ['sometimes', 'required'],
            'email'        => ['sometimes', 'required', 'email'],
            'phone'        => ['sometimes', 'required'],
            'website'      => ['sometimes', 'required', 'url'],
            'tracking_url' => ['sometimes', 'required'],
        ];
    }

    public function action(Shipper $shipper, array $modelData): Shipper
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($shipper, $validatedData);
    }
}
