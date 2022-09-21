<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 14:21:31 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Profile;

use App\Actions\WithActionUpdate;
use App\Http\Resources\SysAdmin\UserResource;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property User $user
 */
class UpdateProfile
{
    use WithActionUpdate;

    public function handle(User $user, array $modelData): User
    {
        return $this->update($user, $modelData, ['data', 'settings']);
    }


    public function rules(): array
    {
        return [
            'username' => 'sometimes|required|alpha_dash|unique:App\Models\SysAdmin\User,username',
            'password' => ['sometimes', 'required', Password::min(8)->uncompromised()],
            'language' => 'sometimes|required|exists:languages,code'
        ];
    }


    public function asController(ActionRequest $request): User
    {
        $validated = $request
            ->validatedShiftToArray([
                                        'language' => 'settings'
                                    ]);


        return $this->handle($request->user(), $validated);
    }

    public function HtmlResponse(): RedirectResponse
    {
        return Redirect::route('welcome');
    }

    public function JsonResponse(User $user): UserResource
    {
        return new UserResource($user);
    }

}
