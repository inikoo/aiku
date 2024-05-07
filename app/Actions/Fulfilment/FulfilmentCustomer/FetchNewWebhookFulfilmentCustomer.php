<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 26 Feb 2024 21:27:03 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;

class FetchNewWebhookFulfilmentCustomer extends OrgAction
{
    use WithActionUpdate;


    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): array
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = UpdateFulfilmentCustomer::run($fulfilmentCustomer, [
            'webhook_access_key' => Str::random(64)
        ]);

        return [
            'webhook_access_key' => $fulfilmentCustomer->webhook_access_key
        ];
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): array
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public function action(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, array $modelData): array
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


}
