<?php

/*
 * author Arya Permana - Kirin
 * created on 20-01-2025-11h-28m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\SysAdmin;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateWebUsers;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\WebUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class DeleteRetinaWebUser extends RetinaAction
{
    use WithActionUpdate;

    private bool $action = false;
    public function handle(WebUser $webUser, array $deletedData = [], bool $skipHydrate = false): void
    {
        $webUser->delete();
        $webUser = $this->update($webUser, $deletedData, ['data']);

        if (!$skipHydrate) {
            CustomerHydrateWebUsers::dispatch($webUser->customer);
        }
    }

    public function authorize(ActionRequest $request)
    {
        if ($this->action) {
            return true;
        }

        return false;
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('retina.sysadmin.web-users.index');
    }

    public function asController(WebUser $webUser, ActionRequest $request): void
    {

        $this->initialisation($request);

        $this->handle($webUser);
    }

    public function action(WebUser $webUser): void
    {
        $this->action = true;
        $this->initialisationFulfilmentActions($webUser->customer->fulfilmentCustomer, []);

        $this->handle($webUser);
    }

}
