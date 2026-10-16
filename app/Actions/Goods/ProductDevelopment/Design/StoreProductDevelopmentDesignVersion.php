<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-10h-50m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\Design;

use App\Actions\Goods\ProductDevelopment\CreateProductDevelopmentTask;
use App\Actions\Goods\ProductDevelopment\RecordProductDevelopmentActivity;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundStatusEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentDesignRound;
use App\Models\SysAdmin\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StoreProductDevelopmentDesignVersion extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ProductDevelopment $productDevelopment;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        $productDevelopment = $request->route('productDevelopment');

        return $this->canViewProductDevelopment($request->user(), $productDevelopment)
            && $this->productDevelopmentAccess()->canDesign($request->user(), $productDevelopment);
    }

    public function handle(ProductDevelopment $productDevelopment, User $user, ProductDevelopmentDesignRoundEnum $round, ?int $itemId = null): ProductDevelopmentDesignRound
    {
        $itemId   = $round->isPerSku() ? $itemId : null;
        $previous = ProductDevelopmentDesignRounds::latest($productDevelopment, $round, $itemId)
            ?? ($round->previous() ? ProductDevelopmentDesignRounds::latest($productDevelopment, $round->previous()) : null);

        $layout = $previous?->data['layout'] ?? null;

        /** @var ProductDevelopmentDesignRound $designRound */
        $designRound = $productDevelopment->designRounds()->create([
            'round'                       => $round,
            'product_development_item_id' => $itemId,
            'version'                     => (int) ProductDevelopmentDesignRounds::scope($productDevelopment, $round, $itemId)->max('version') + 1,
            'status'                      => ProductDevelopmentDesignRoundStatusEnum::DRAFT,
            'data'                        => ['layout' => $layout ? ProductDevelopmentDesignLayout::normalise($layout) : ProductDevelopmentDesignLayout::blank($productDevelopment)],
        ]);

        RecordProductDevelopmentActivity::record($productDevelopment, RecordProductDevelopmentActivity::DESIGN_VERSION_STARTED, [], [
            'round'   => $round->value,
            'version' => $designRound->version,
            'sku'     => $designRound->item?->tradeUnit?->code,
            'by'      => $user->contact_name ?: $user->username,
        ]);

        CreateProductDevelopmentTask::make()->syncProgress($productDevelopment, $user);

        return $designRound;
    }

    public function rules(): array
    {
        return [
            'round'                       => ['required', Rule::enum(ProductDevelopmentDesignRoundEnum::class)],
            'product_development_item_id' => ['required_if:round,'.ProductDevelopmentDesignRoundEnum::LABEL->value, 'nullable', 'integer', Rule::exists('product_development_items', 'id')->where('product_development_id', $this->productDevelopment->id)],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $round  = ProductDevelopmentDesignRoundEnum::tryFrom((string) $this->get('round'));
        $itemId = $this->get('product_development_item_id') ? (int) $this->get('product_development_item_id') : null;
        if (!$round || ($round->isPerSku() && !$itemId) || ProductDevelopmentDesignRounds::canStartVersion($this->productDevelopment, $round, $itemId)) {
            return;
        }

        $validator->errors()->add('round', match (true) {
            ProductDevelopmentDesignRounds::isArtworkLocked($this->productDevelopment) => __('The artwork is signed off and locked'),
            !ProductDevelopmentDesignRounds::isUnlocked($this->productDevelopment, $round) => __('Approve the previous round first'),
            default => __('This round already has a version in progress or approved'),
        });
    }

    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request): ProductDevelopmentDesignRound
    {
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $request);

        return $this->handle($productDevelopment, $request->user(), ProductDevelopmentDesignRoundEnum::from($this->validatedData['round']), $this->validatedData['product_development_item_id'] ?? null);
    }

    public function action(ProductDevelopment $productDevelopment, User $user, array $modelData): ProductDevelopmentDesignRound
    {
        $this->asAction           = true;
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $modelData);

        return $this->handle($productDevelopment, $user, ProductDevelopmentDesignRoundEnum::from($this->validatedData['round']), $this->validatedData['product_development_item_id'] ?? null);
    }

    public function htmlResponse(ProductDevelopmentDesignRound $designRound): RedirectResponse
    {
        return Redirect::route('grp.product_developments.design', [
            'productDevelopment' => $designRound->productDevelopment->slug,
            'round'              => $designRound->round->value,
            'version'            => $designRound->version,
            'sku'                => $designRound->product_development_item_id,
        ]);
    }
}
