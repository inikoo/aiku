<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipper;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dispatching\Shipper;
use App\Rules\IUnique;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;

class UpdateShipper extends OrgAction
{
    use WithActionUpdate;


    private Shipper $shipper;

    public function handle(Shipper $shipper, array $modelData): Shipper
    {
        return $this->update($shipper, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("dispatching.{$this->organisation->id}.edit");
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($this->has('website') and $this->get('website') != null) {
            if (!Str::startsWith($this->get('website'), 'http')) {
                $this->fill(['website' => 'https://'.$this->get('website')]);
            }
        }
    }


    public function rules(): array
    {
        return [
            'code'         => [
                'required',
                'between:2,16',
                'alpha_dash',
                new IUnique(
                    table: 'shippers',
                    extraConditions: [
                        [
                            'column' => 'group_id',
                            'value'  => $this->organisation->group_id
                        ],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->shipper->id
                        ],
                    ]
                ),
            ],
            'name'         => ['sometimes', 'required', 'max:255', 'string'],
            'api_shipper'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email'        => ['sometimes', 'nullable', 'email'],
            'phone'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'website'      => ['sometimes', 'nullable', 'url'],
            'tracking_url' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function action(Shipper $shipper, array $modelData): Shipper
    {
        $this->asAction = true;
        $this->shipper  = $shipper;

        $this->initialisation($shipper->organisation, $modelData);

        return $this->handle($shipper, $this->validatedData);
    }
}
