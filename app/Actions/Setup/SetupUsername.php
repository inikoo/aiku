<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 12 Aug 2022 02:53:09 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Setup;

use App\Models\Organisations\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property User $user
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
            'username' => 'required|alpha_dash|unique:App\Models\Organisations\User,username',
        ];
    }

    /** @noinspection PhpUnused */
    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->user = $request->user();
        $this->handle($request->only(['username']));

        return Redirect::route('setup.root');
    }
}
