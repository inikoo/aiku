<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-19h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Json;

use App\Actions\Goods\ProductDevelopment\StoreProductDevelopment;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Models\Goods\TradeUnit;
use App\Models\SysAdmin\Group;
use Illuminate\Support\Facades\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * Tells the create form, while the user types, whether a new SKU code would be accepted on save.
 */
class CheckProductDevelopmentSkuCode extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    public function authorize(ActionRequest $request): bool
    {
        return $this->canCreateProductDevelopment($request->user());
    }

    /**
     * @return array{valid: bool, message: string|null}
     */
    public function handle(Group $group, string $code): array
    {
        $code = trim($code);
        if ($code === '') {
            return ['valid' => true, 'message' => null];
        }

        $validator = Validator::make(['code' => $code], ['code' => StoreProductDevelopment::skuCodeRules($group->id)], [
            'code.max'    => __('The code can be at most 64 characters'),
            'code.not_in' => __('This word cannot be used as a code'),
        ], ['code' => __('code')]);

        if ($validator->passes()) {
            return ['valid' => true, 'message' => null];
        }

        $taken = TradeUnit::where('group_id', $group->id)->whereRaw('lower(code) = ?', [mb_strtolower($code)])->first(['code', 'name']);

        return [
            'valid'   => false,
            'message' => $taken
                ? __('Already used by trade unit :code · :name', ['code' => $taken->code, 'name' => $taken->name])
                : $validator->errors()->first('code'),
        ];
    }

    public function rules(): array
    {
        return [
            'code' => ['present', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array{valid: bool, message: string|null}
     */
    public function asController(ActionRequest $request): array
    {
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($this->group, (string) $this->validatedData['code']);
    }
}
