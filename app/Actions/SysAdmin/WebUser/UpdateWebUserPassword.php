<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 16 Oct 2023 15:27:31 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\WebUser;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\WebUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

class UpdateWebUserPassword extends OrgAction
{
    use WithActionUpdate;

    private bool $asAction = false;

    public function handle(WebUser $webUser, array $modelData): WebUser
    {
        data_set($modelData, 'reset_password', false);
        return $this->update($webUser, $modelData, 'settings');
    }


    public function rules(): array
    {
        return [
            'password' => ['required', app()->isLocal() || app()->environment('testing') ? null : Password::min(8)->uncompromised()],
        ];
    }


    public function asController(Organisation $organisation, ActionRequest $request): WebUser
    {
        $this->initialisation($organisation, $request);

        return $this->handle($request->user(), $this->validatedData);
    }

    public function action(WebUser $webUser, $objectData): WebUser
    {
        $this->asAction = true;
        $this->initialisation($webUser->organisation, $objectData);

        return $this->handle($webUser, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        Session::put('reloadLayout', '1');

        return Redirect::route('retina.dashboard.show');
    }
}
