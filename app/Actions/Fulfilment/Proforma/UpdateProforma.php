<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Feb 2024 21:27:03 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Proforma;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\FulfilmentProforma;
use Lorisleiva\Actions\ActionRequest;

class UpdateProforma extends OrgAction
{
    use WithActionUpdate;


    public function handle(FulfilmentProforma $fulfilmentProforma, array $modelData): FulfilmentProforma
    {

        return $this->update($fulfilmentProforma, $modelData, ['data']);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'total'            => ['sometimes'],
            'payment'          => ['sometimes'],
            'net'              => ['sometimes'],
            'group_net_amount' => ['sometimes'],
            'org_net_amount'   => ['sometimes']
        ];
    }


    public function asController(FulfilmentProforma $fulfilmentProforma, ActionRequest $request): FulfilmentProforma
    {
        $this->initialisationFromFulfilment($fulfilmentProforma->fulfilment, $request);

        return $this->handle($fulfilmentProforma, $this->validatedData);
    }

    public function action(FulfilmentProforma $fulfilmentProforma, array $modelData): FulfilmentProforma
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($fulfilmentProforma->fulfilment, $modelData);

        return $this->handle($fulfilmentProforma, $this->validatedData);
    }


}
