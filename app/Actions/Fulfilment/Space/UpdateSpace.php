<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 30 Jan 2025 18:10:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Space;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithFulfilmentAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Billables\Rental\RentalTypeEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Space;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateSpace extends OrgAction
{
    use WithFulfilmentAuthorisation;
    use WithActionUpdate;

    public function handle(Space $space, array $modelData): Space
    {
        return $this->update($space, $modelData);
    }

    public function rules(): array
    {
        return [
            'reference'       => ['sometimes', 'max:64', 'string'],
            'exclude_weekend' => ['sometimes', 'bool'],
            'start_at'        => ['sometimes', 'date'],
            'end_at'          => ['sometimes', 'date'],
            'rental_id'       => ['sometimes', 'integer', Rule::exists('rentals', 'id')
                ->where('fulfilment_id', $this->fulfilment->id)
                ->where('type', RentalTypeEnum::SPACE)
            ],
        ];
    }

    public function asController(FulfilmentCustomer $fulfilmentCustomer, Space $space, ActionRequest $request): Space
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($space, $this->validatedData);
    }

    public function action(Space $space, array $modelData): Space
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($space->fulfilment, $modelData);

        return $this->handle($space, $this->validatedData);
    }
}
