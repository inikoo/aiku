<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\Shipper;

use App\Models\Dispatch\Shipper;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShipper
{
    use AsAction;
    use WithAttributes;

    public function handle(array $modelData): Shipper
    {
        return Shipper::create($modelData);
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

    public function action(array $objectData): Shipper
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }
}
