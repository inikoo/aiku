<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 13:13:31 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\Setup;

use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property \App\Models\SysAdmin\User $user
 */
class SetupUsername
{
    use AsAction;

    public function handle(array $data): void
    {
        $this->user->update($data);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|alpha_dash|unique:App\Models\Auth\User,username',
        ];
    }

    /** @noinspection PhpUnused */
    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if ($request->get('username')!=strtolower($request->get('username'))  ) {
            $validator->errors()->add('invalid_username', 'Username must be lowercase.');
        }
    }

    /** @noinspection PhpUnused */
    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->user = $request->user();
        $this->handle($request->only(['username']));

        return Redirect::route('setup.root');
    }
}
