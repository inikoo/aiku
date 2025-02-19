<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 31 Jan 2025 14:28:27 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Spaces;

use App\Actions\RetinaAction;
use App\Actions\Traits\Authorisations\WithFulfilmentAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Billables\Rental\RentalTypeEnum;
use App\Models\Fulfilment\Space;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateRetinaSpace extends RetinaAction
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

    public function asController(Space $space, ActionRequest $request): Space
    {
        $this->initialisation($request);

        return $this->handle($space, $this->validatedData);
    }
}
