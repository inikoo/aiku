<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\Shipper;

use App\Actions\Dispatching\Shipper\Hydrators\ShipperHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Models\Dispatching\Shipper;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;

class StoreShipper extends OrgAction
{
    public function handle(Organisation $organisation, array $modelData): Shipper
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        /** @var Shipper $shipper */
        $shipper= $organisation->shippers()->create($modelData);

        ShipperHydrateUniversalSearch::dispatch($shipper);

        return $shipper;
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

            if(!Str::startsWith($this->get('website'), 'http')) {
                $this->fill(['website' => 'https://' . $this->get('website')]);
            }
        }
    }


    public function rules(): array
    {
        return [
            'code'         => ['required',  'between:2,16', 'alpha_dash',
                               new IUnique(
                                   table: 'shippers',
                                   extraConditions: [
                                       ['column' => 'group_id', 'value' => $this->organisation->group_id],
                                   ]
                               ),
                ],
            'name'         => ['required', 'max:255', 'string'],
            'api_shipper'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_name' => ['sometimes','nullable',  'string', 'max:255'],
            'email'        => ['sometimes','nullable',  'email'],
            'phone'        => ['sometimes','nullable', 'string', 'max:255'],
            'website'      => ['sometimes', 'nullable', 'url'],
            'tracking_url' => ['sometimes','nullable',  'string', 'max:255'],
            'source_id'    => ['sometimes', 'nullable', 'string', 'max:64'],
        ];
    }

    public function action(Organisation $organisation, $modelData): Shipper
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $this->validatedData);
    }
}
