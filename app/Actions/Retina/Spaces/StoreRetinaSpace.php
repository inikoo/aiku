<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 31 Jan 2025 14:28:27 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Spaces;

use App\Actions\Fulfilment\Space\StoreSpace;
use App\Actions\Fulfilment\UI\WithFulfilmentAuthorisation;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Billables\Rental\RentalTypeEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Space;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redirect;

class StoreRetinaSpace extends RetinaAction
{
    use WithFulfilmentAuthorisation;
    use WithActionUpdate;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): Space
    {
        return StoreSpace::run($fulfilmentCustomer, $modelData);
    }

    public function rules(): array
    {
        return [
            'reference'       => ['required', 'max:64', 'string'],
            'exclude_weekend' => ['required', 'bool'],
            'start_at'        => ['required', 'date'],
            'end_at'          => ['nullable', 'date'],
            'rental_id'       => ['required', 'integer', Rule::exists('rentals', 'id')
                ->where('fulfilment_id', $this->fulfilment->id)
                ->where('type', RentalTypeEnum::SPACE)
            ],
        ];
    }

    public function htmlResponse(Space $space): Response
    {
        return Redirect::route('grp.org.fulfilments.show.crm.customers.show.spaces.index', [
            'organisation'       => $space->organisation->slug,
            'fulfilment'         => $space->fulfilment->slug,
            'fulfilmentCustomer' => $space->fulfilmentCustomer->slug
        ]);
    }

    public function asController(ActionRequest $request): Space
    {
        $this->initialisation($request);

        return $this->handle($this->fulfilmentCustomer, $this->validatedData);
    }
}
