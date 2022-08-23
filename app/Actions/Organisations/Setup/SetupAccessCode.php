<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 13:13:31 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\Setup;

use App\Actions\Organisations\User\SetAvatar;
use App\Actions\Organisations\User\UpdateUser;
use App\Actions\Organisations\User\UpdateUserFromSource;
use App\Models\Organisations\User;
use App\Models\Organisations\UserLinkCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property UserLinkCode userLinkCode
 * @property User user
 */
class SetupAccessCode
{
    use AsAction;


    public function handle(): void
    {
        $this->userLinkCode->organisation->users()->attach($this->user->id);

        UpdateUser::run($this->user, [
            'data' =>
                [
                    'source' => $this->userLinkCode->organisation->type,
                    'source_id' => $this->userLinkCode->source_user_id
                ]
        ]);

        $this->userLinkCode->delete();
        $this->user->refresh();
        UpdateUserFromSource::run($this->user);
        $this->user->refresh();
        if(!Arr::get($this->user->data,'profile_url')){
            SetAvatar::run($this->user);
        }

    }


    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|exists:App\Models\Organisations\UserLinkCode',
        ];
    }

    /** @noinspection PhpUnused */
    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        $this->userLinkCode = UserLinkCode::where('code', $request->get('code'))->withTrashed()->first();

        if ($this->userLinkCode and $this->userLinkCode->trashed()) {
            $validator->errors()->add('expired_code', 'Access code expired.');
        }
    }


    /** @noinspection PhpUnused */
    public function asController(ActionRequest $request): RedirectResponse
    {
        $this->user = $request->user();
        $this->handle();

        return Redirect::route('setup.root');
    }
}
