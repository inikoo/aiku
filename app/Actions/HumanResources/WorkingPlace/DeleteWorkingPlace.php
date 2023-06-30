<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\WorkingPlace;

use App\Models\HumanResources\Workplace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteWorkingPlace
{
    use AsController;
    use WithAttributes;

    public function handle(Workplace $workplace): Workplace
    {
        $workplace->delete();

        return $workplace;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.edit");
    }

    public function asController(Workplace $workplace, ActionRequest $request): Workplace
    {
        $request->validate();

        return $this->handle($workplace);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('hr.working-places.index');
    }

}
