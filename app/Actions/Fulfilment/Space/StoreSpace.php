<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:32:25 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Space;

use App\Actions\Fulfilment\UI\WithFulfilmentAuthorisation;
use App\Actions\OrgAction;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Space;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreSpace extends OrgAction
{
    use WithFulfilmentAuthorisation;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): Space
    {
        data_set($modelData, 'organisation_id', $fulfilmentCustomer->organisation_id);
        data_set($modelData, 'group_id', $fulfilmentCustomer->group_id);
        data_set($modelData, 'fulfilment_id', $fulfilmentCustomer->fulfilment_id);

        return $fulfilmentCustomer->spaces()->create($modelData);
    }

    public function rules(): array
    {
        return [
            'reference' => ['required',  'max:64', 'string'],
            'exclude_weekend' => ['required', 'bool'],
            'start_at' => ['required', 'date'],
            'end_at' => ['nullable', 'date'],
            'rental_id' => ['required', 'integer', 'exists:rentals,id'],
        ];
    }

    public function htmlResponse(Space $space): Response
    {
        return Inertia::location(route('grp.org.fulfilments.show.crm.customers.show.spaces.index', [
            'organisation' => $space->organisation->slug,
            'fulfilment' => $space->fulfilment->slug,
            'fulfilmentCustomer' => $space->fulfilmentCustomer->slug
        ]));
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): Space
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }
}
