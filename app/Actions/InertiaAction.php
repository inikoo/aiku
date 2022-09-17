<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 17 Sept 2022 02:10:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Models\Organisations\Organisation;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class InertiaAction
{
    use AsAction;
    use WithAttributes;
    protected ?string $routeName = null;
    protected Organisation $organisation;


    public function prepareForValidation(ActionRequest $request): void
    {
        $user               = $request->user();
        $this->organisation = $user->currentUiOrganisation;
        $this->routeName    = $request->route()->getName();
    }


    /**
     * @throws \Exception
     * @noinspection PhpUnused
     */
    public function getValidationFailure(): void
    {
        abort(422);
    }



}

