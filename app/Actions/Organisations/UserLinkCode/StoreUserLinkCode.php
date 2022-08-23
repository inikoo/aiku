<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 18 Aug 2022 14:13:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\UserLinkCode;


use App\Http\Resources\Utils\ActionResultResource;
use App\Models\Organisations\Organisation;
use App\Models\Organisations\UserLinkCode;
use App\Models\Utils\ActionResult;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreUserLinkCode
{
    use AsAction;

    public function handle(Organisation $organisation, array $modelData): ActionResult
    {
        $res = new ActionResult();

        $modelData['code']=wordwrap(Str::random(8), 4, '-', true);

        /** @var \App\Models\Organisations\UserLinkCode $userLinkCode */
        $userLinkCode = $organisation->userLinkCodes()->create($modelData);


        $res->model    = $userLinkCode;
        $res->model_id = $userLinkCode->id;
        $res->status   = $res->model_id ? 'inserted' : 'error';

        return $res;
    }

    public function authorize(ActionRequest $request): bool
    {

        return $request->user()->tokenCan('bridge') ;
    }

    public function rules(ActionRequest $request): array
    {
        return [
            'source_user_id' => [
                'required'
            ],
        ];

    }

    /** @noinspection PhpUnused */
    public function afterValidator(Validator $validator, ActionRequest $request): void
    {

        if($userLinkCode=UserLinkCode::where('organisation_id',$request->user()->id)->where('source_user_id',$request->get('source_user_id'))->first()){
            $validator->errors()->add('source_user_id_taken',$userLinkCode);

        }

    }


    /** @noinspection PhpUnused */
    public function asController( ActionRequest $request): ActionResultResource
    {
        $organisation=$request->user();
        $actionResult= $this->handle(
            $organisation,
            $request->validated());
        return new ActionResultResource($actionResult);


    }
}
