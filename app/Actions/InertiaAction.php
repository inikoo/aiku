<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 17 Sept 2022 02:10:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class InertiaAction
{
    use AsAction;
    use WithAttributes;

    protected ?string $routeName        = null;
    protected array $originalParameters = [];
    protected bool $can_edit            =false;

    public function initialisation(ActionRequest $request): void
    {
        $this->routeName         = $request->route()->getName();
        $this->originalParameters=$request->route()->originalParameters();
        $request->validate();
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
