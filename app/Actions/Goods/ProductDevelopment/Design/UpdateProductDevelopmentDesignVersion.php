<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-11h-00m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundStatusEnum;
use App\Models\Goods\ProductDevelopmentDesignRound;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class UpdateProductDevelopmentDesignVersion extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ProductDevelopmentDesignRound $designRound;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $request->route('productDevelopmentDesignRound')->productDevelopment;

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->productDevelopmentAccess()->canDesign($request->user(), $productDevelopment);
    }

    public function handle(ProductDevelopmentDesignRound $designRound, User $user, array $modelData): ProductDevelopmentDesignRound
    {
        $designRound->update([
            'notes' => trim((string) ($modelData['notes'] ?? '')) ?: null,
        ]);

        return $designRound;
    }

    public function rules(): array
    {
        return [
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (ProductDevelopmentDesignRounds::isArtworkLocked($this->designRound->productDevelopment)) {
            $validator->errors()->add('notes', __('The artwork is signed off and locked'));

            return;
        }

        if ($this->designRound->status !== ProductDevelopmentDesignRoundStatusEnum::DRAFT) {
            $validator->errors()->add('notes', __('Only a draft version can be changed'));
        }
    }

    public function asController(ProductDevelopmentDesignRound $productDevelopmentDesignRound, ActionRequest $request): ProductDevelopmentDesignRound
    {
        $this->designRound = $productDevelopmentDesignRound;
        $this->initialisationFromGroup($productDevelopmentDesignRound->productDevelopment->group, $request);

        return $this->handle($productDevelopmentDesignRound, $request->user(), $this->validatedData);
    }

    public function action(ProductDevelopmentDesignRound $designRound, User $user, array $modelData): ProductDevelopmentDesignRound
    {
        $this->asAction    = true;
        $this->designRound = $designRound;
        $this->initialisationFromGroup($designRound->productDevelopment->group, $modelData);

        return $this->handle($designRound, $user, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
