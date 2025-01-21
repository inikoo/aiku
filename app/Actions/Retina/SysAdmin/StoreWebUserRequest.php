<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\SysAdmin;

use App\Actions\GrpAction;
use App\Models\Analytics\WebUserRequest;
use App\Models\CRM\WebUser;
use Lorisleiva\Actions\ActionRequest;

class StoreWebUserRequest extends GrpAction
{
    public function handle(WebUser $webUser, array $modelData): WebUserRequest
    {
        data_set($modelData, 'group_id', $webUser->group_id);
        data_set($modelData, 'website_id', $webUser->website_id);
        $webUserRequest = $webUser->webuserRequests()->create($modelData);
        // GroupHydrateWebUserRequests::dispatch($webuser->group)->delay($this->hydratorsDelay);

        return $webUserRequest;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'date'                   => ['required', 'date'],
            'os'                     => ['required', 'string'],
            'route_name'             => ['required', 'string'],
            'route_params'           => ['required'],
            'aiku_scoped_section_id' => ['nullable', 'integer'],
            'device'                 => ['required', 'string'],
            'browser'                => ['required', 'string'],
            'ip_address'             => ['required', 'string'],
            'location'               => ['required']
        ];
    }

    public function action(WebUser $webUser, array $modelData, int $hydratorsDelay = 0): WebUserRequest
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisation($webUser->group, $modelData);

        return $this->handle($webUser, $this->validatedData);
    }
}
