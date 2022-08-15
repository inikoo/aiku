<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 12 Aug 2022 02:53:09 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */


namespace App\Actions\Setup;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;


/**
 * @property User $user
 */
class SetupUsername
{
    use AsAction;
    use WithAttributes;

    public function handle(User $user, array $data): void
    {
        $user->update($data);
    }


    public function authorize(): bool
    {
        return true;

    }
    public function rules(): array
    {
        return [
            'username' => 'required|alpha_dash|unique:App\Models\User,username',
        ];
    }


    public function asInertia(User $user, Request $request): RedirectResponse
    {
        $this->set('user', $user);

        $this->fillFromRequest($request);
        $this->validateAttributes();

        $this->handle(
            $user,
            $request->only(['username']),
        );

        return Redirect::route('setup.root');
    }
}
