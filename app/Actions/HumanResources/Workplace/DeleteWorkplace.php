<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Workplace;

use App\Actions\InertiaOrganisationAction;
use App\Models\HumanResources\Workplace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteWorkplace extends InertiaOrganisationAction
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
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->slug}.edit");
    }

    public function asController(Workplace $workplace, ActionRequest $request): Workplace
    {
        $request->validate();

        return $this->handle($workplace);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('grp.org.hr.workplaces.index');
    }

}
