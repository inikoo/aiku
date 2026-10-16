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

    public const string SOURCE_EDITOR = 'editor';
    public const string SOURCE_UPLOAD = 'upload';

    public function handle(ProductDevelopment $productDevelopment, User $user, ProductDevelopmentDesignRoundEnum $round, ?int $itemId = null, ?string $source = null): ProductDevelopmentDesignRound
    {
        $itemId = $round->isPerSku() ? $itemId : null;
        $source = $round->canBeBuiltInAiku() ? ($source ?? self::SOURCE_EDITOR) : self::SOURCE_UPLOAD;

        /** @var ProductDevelopmentDesignRound $designRound */
        $designRound = $productDevelopment->designRounds()->create([
            'round'                       => $round,
            'product_development_item_id' => $itemId,
            'version'                     => (int) ProductDevelopmentDesignRounds::scope($productDevelopment, $round, $itemId)->max('version') + 1,
            'status'                      => ProductDevelopmentDesignRoundStatusEnum::DRAFT,
            'data'                        => $source === self::SOURCE_UPLOAD
                ? ['source' => self::SOURCE_UPLOAD, 'upload' => null]
                : ['source' => self::SOURCE_EDITOR, 'layout' => $this->startingLayout($productDevelopment, $round, $itemId)],
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

    /**
     * A version built in Aiku starts from the last one built in Aiku for the same round (and SKU); a first
     * packaging starts as the box sized from the SKU, a first label as an empty page.
     *
     * @return array<string, mixed>
     */
    private function startingLayout(ProductDevelopment $productDevelopment, ProductDevelopmentDesignRoundEnum $round, ?int $itemId): array
    {
        $previous = ProductDevelopmentDesignRounds::scope($productDevelopment, $round, $itemId)
            ->orderByDesc('version')
            ->get()
            ->first(fn (ProductDevelopmentDesignRound $designRound) => ($designRound->data['source'] ?? self::SOURCE_EDITOR) === self::SOURCE_EDITOR && !empty($designRound->data['layout']));

        if ($previous) {
            return ProductDevelopmentDesignLayout::normalise($previous->data['layout']);
        }

        return $round === ProductDevelopmentDesignRoundEnum::LABEL
            ? ProductDevelopmentDesignLayout::blankSheet()
            : ProductDevelopmentDesignLayout::blank($productDevelopment);
    }

    public function rules(): array
    {
        return [
            'round'                       => ['required', Rule::enum(ProductDevelopmentDesignRoundEnum::class)],
            'product_development_item_id' => ['required_if:round,'.ProductDevelopmentDesignRoundEnum::LABEL->value, 'nullable', 'integer', Rule::exists('product_development_items', 'id')->where('product_development_id', $this->productDevelopment->id)],
            'source'                      => ['sometimes', 'nullable', Rule::in([self::SOURCE_EDITOR, self::SOURCE_UPLOAD])],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        $round  = ProductDevelopmentDesignRoundEnum::tryFrom((string) $this->get('round'));
        $itemId = $this->get('product_development_item_id') ? (int) $this->get('product_development_item_id') : null;
        if ($round && !$round->canBeBuiltInAiku() && $this->get('source') === self::SOURCE_EDITOR) {
            $validator->errors()->add('source', __('The concept is uploaded as a PDF or picture'));

            return;
        }

        if (!$round || ($round->isPerSku() && !$itemId) || ProductDevelopmentDesignRounds::canStartVersion($this->productDevelopment, $round, $itemId)) {
            return;
        }

        $validator->errors()->add('round', match (true) {
            ProductDevelopmentDesignRounds::isArtworkLocked($this->productDevelopment) => __('The artwork is signed off and locked'),
            ProductDevelopmentDesignRounds::isSkipped($this->productDevelopment, $round) => __('This round was skipped'),
            !ProductDevelopmentDesignRounds::isUnlocked($this->productDevelopment, $round) => __('Approve the previous round first'),
            default => __('This round already has a version in progress or approved'),
        });
    }

    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request): ProductDevelopmentDesignRound
    {
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $request);

        return $this->handle($productDevelopment, $request->user(), ProductDevelopmentDesignRoundEnum::from($this->validatedData['round']), $this->validatedData['product_development_item_id'] ?? null, $this->validatedData['source'] ?? null);
    }

    public function action(ProductDevelopment $productDevelopment, User $user, array $modelData): ProductDevelopmentDesignRound
    {
        $this->asAction           = true;
        $this->productDevelopment = $productDevelopment;
        $this->initialisationFromGroup($productDevelopment->group, $modelData);

        return $this->handle($productDevelopment, $user, ProductDevelopmentDesignRoundEnum::from($this->validatedData['round']), $this->validatedData['product_development_item_id'] ?? null, $this->validatedData['source'] ?? null);
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
