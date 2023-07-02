<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 17 Nov 2022 12:23:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Models\Auth\WebUser;
use App\Models\Central\Domain;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;

class fromIris
{
    use AsController;


    public function authorize(ActionRequest $request): bool
    {
        if ($request->user()->tokenCan('iris') or $request->user()->tokenCan('root-iris')) {
            return true;
        }

        return false;
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->user()->userable_type == 'Domain') {
            $request->merge(['central_domain_id' => $request->user()->userable_id]);
        }
    }

    public function baseRules(): array
    {
        return [
            'central_domain_id' => ['required', 'exists:domains,id'],
            'web_user_id'       => ['required', 'integer'],
        ];
    }


    public function asController(ActionRequest $request)
    {
        $validated     = $request->validated();
        $domain        = Domain::findOrFail(Arr::get($validated, 'central_domain_id'));

        return $domain->tenant->run(
            function () use ($request) {
                $webUser = WebUser::findOrFail($request->get('web_user_id'));

                return $this->handle(
                    $webUser,
                    Arr::except($request->validated(), ['central_domain_id', 'web_user_id'])
                );
            }
        );
    }
}
