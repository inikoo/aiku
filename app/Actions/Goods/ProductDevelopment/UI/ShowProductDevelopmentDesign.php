<?php

/*
 * Author Louis Perez
 * Created on 17-10-2026-12h-20m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignImages;
use App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignLayout;
use App\Actions\Goods\ProductDevelopment\Design\ProductDevelopmentDesignRounds;
use App\Actions\Goods\ProductDevelopment\Design\StoreProductDevelopmentDesignVersion;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundStatusEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentDesignRound;
use App\Models\SysAdmin\User;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowProductDevelopmentDesign extends OrgAction
{
    use WithProductDevelopmentAuthorisation;

    private ProductDevelopmentDesignRoundEnum $round;

    private ?int $versionNumber = null;

    private ?int $itemId = null;

    public function handle(ProductDevelopment $productDevelopment): ProductDevelopment
    {
        return $productDevelopment->load(['designRounds.submittedBy', 'designRounds.approvedBy', 'owner']);
    }

    public function asController(ProductDevelopment $productDevelopment, ActionRequest $request, ?string $round = null): ProductDevelopment
    {
        $this->initialisationFromGroup(group(), $request);
        $this->round         = $round ? ProductDevelopmentDesignRoundEnum::from($round) : $this->currentRound($productDevelopment);
        $this->versionNumber = $request->integer('version') ?: null;
        $this->itemId        = $this->round->isPerSku() ? $this->pickItem($productDevelopment, $request->integer('sku') ?: null) : null;

        return $this->handle($productDevelopment);
    }

    /**
     * The SKU asked for, else the first SKU whose label is not approved yet, else the first SKU.
     */
    private function pickItem(ProductDevelopment $productDevelopment, ?int $requested): ?int
    {
        $itemIds = $productDevelopment->items()->orderBy('id')->pluck('id');
        if ($requested && $itemIds->contains($requested)) {
            return $requested;
        }

        return $itemIds->first(fn (int $itemId) => !ProductDevelopmentDesignRounds::isApproved($productDevelopment, $this->round, $itemId)) ?? $itemIds->first();
    }

    /**
     * The first round without an approved version, so the page opens where the work is.
     */
    private function currentRound(ProductDevelopment $productDevelopment): ProductDevelopmentDesignRoundEnum
    {
        foreach (ProductDevelopmentDesignRoundEnum::cases() as $round) {
            if (!ProductDevelopmentDesignRounds::isSettled($productDevelopment, $round)) {
                return $round;
            }
        }

        return ProductDevelopmentDesignRoundEnum::LABEL;
    }

    public function htmlResponse(ProductDevelopment $productDevelopment, ActionRequest $request): Response
    {
        $user     = $request->user();
        $access   = $this->productDevelopmentAccess();
        $versions = $productDevelopment->designRounds
            ->where('round', $this->round)
            ->where('product_development_item_id', $this->itemId)
            ->sortBy('version')
            ->values();

        /** @var ProductDevelopmentDesignRound|null $selected */
        $selected = ($this->versionNumber ? $versions->firstWhere('version', $this->versionNumber) : null) ?? $versions->last();
        $previous = $selected ? $versions->where('version', '<', $selected->version)->last() : null;

        $canDesign  = $access->canDesign($user, $productDevelopment);
        $canApprove = $access->canApproveDesign($user, $productDevelopment);
        $roundLabel = ProductDevelopmentDesignRoundEnum::labels()[$this->round->value];

        return Inertia::render(
            'Goods/ProductDevelopmentDesign',
            [
                'title'       => $productDevelopment->code.' '.__('Design'),
                'breadcrumbs' => $this->getBreadcrumbs($productDevelopment),
                'pageHead'    => [
                    'icon'  => [
                        'title' => __('Design'),
                        'icon'  => 'fal fa-pencil-ruler',
                    ],
                    'model' => $productDevelopment->code,
                    'title' => $productDevelopment->name,
                ],
                'rounds'      => collect(ProductDevelopmentDesignRoundEnum::cases())->map(fn (ProductDevelopmentDesignRoundEnum $round) => [
                    'value'    => $round->value,
                    'label'    => ProductDevelopmentDesignRoundEnum::labels()[$round->value],
                    'icon'     => $round->icon(),
                    'unlocked' => ProductDevelopmentDesignRounds::isUnlocked($productDevelopment, $round),
                    'skipped'  => ProductDevelopmentDesignRounds::isSkipped($productDevelopment, $round),
                    'status'   => $round->isPerSku() ? null : $this->status(ProductDevelopmentDesignRounds::latest($productDevelopment, $round)),
                    'version'  => $round->isPerSku() ? null : ProductDevelopmentDesignRounds::latest($productDevelopment, $round)?->version,
                    'progress' => $round->isPerSku() ? ProductDevelopmentDesignRounds::skuProgress($productDevelopment, $round) : null,
                    'route'    => ['name' => 'grp.product_developments.design', 'parameters' => ['productDevelopment' => $productDevelopment->slug, 'round' => $round->value]],
                ])->all(),
                'round'       => ['value' => $this->round->value, 'label' => $roundLabel, 'per_sku' => $this->round->isPerSku()],
                'skus'        => $this->round->isPerSku() ? $productDevelopment->items()->with('tradeUnit')->orderBy('id')->get()->map(fn ($item) => [
                    'id'      => $item->id,
                    'code'    => $item->tradeUnit->code,
                    'name'    => $item->tradeUnit->name,
                    'status'  => $this->status(ProductDevelopmentDesignRounds::latest($productDevelopment, $this->round, $item->id)),
                    'version' => ProductDevelopmentDesignRounds::latest($productDevelopment, $this->round, $item->id)?->version,
                    'route'   => ['name' => 'grp.product_developments.design', 'parameters' => ['productDevelopment' => $productDevelopment->slug, 'round' => $this->round->value, 'sku' => $item->id]],
                ])->all() : [],
                'sku'         => $this->itemId,
                'labelContent' => $this->round->isPerSku() && $this->itemId
                    ? GetProductDevelopmentLabelContent::run($productDevelopment, $productDevelopment->items()->with('tradeUnit')->findOrFail($this->itemId))
                    : null,
                'artworkLocked' => ProductDevelopmentDesignRounds::isArtworkLocked($productDevelopment),
                'skip'          => $this->skipState($productDevelopment, $user),
                'versions'    => $versions->map(fn (ProductDevelopmentDesignRound $designRound) => [
                    'id'           => $designRound->id,
                    'version'      => $designRound->version,
                    'status'       => $this->status($designRound),
                    'submitted_by' => $this->name($designRound->submittedBy),
                    'submitted_at' => $designRound->submitted_at?->toIso8601String(),
                    'created_at'   => $designRound->created_at?->toIso8601String(),
                    'comments'     => $designRound->comments()->whereNull('parent_id')->count(),
                    'route'        => ['name' => 'grp.product_developments.design', 'parameters' => array_filter(['productDevelopment' => $productDevelopment->slug, 'round' => $this->round->value, 'version' => $designRound->version, 'sku' => $this->itemId])],
                ])->reverse()->values()->all(),
                'version'     => $selected ? $this->version($selected, $user, $canDesign, $canApprove) : null,
                'previous'    => $previous ? ['version' => $previous->version, ...self::content($previous)] : null,
                'images'      => ProductDevelopmentDesignImages::forDevelopment($productDevelopment),
                'dataSources' => ProductDevelopmentDesignLayout::dataSources(),
                'comments'    => $selected ? GetProductDevelopmentComments::run($productDevelopment, $selected) : null,
                'commentStatusRoute' => 'grp.models.product_development.comment.status',
                'startVersion' => $canDesign && ProductDevelopmentDesignRounds::canStartVersion($productDevelopment, $this->round, $this->itemId) ? [
                    'route'     => ['name' => 'grp.models.product_development.design.store', 'parameters' => ['productDevelopment' => $productDevelopment->id]],
                    'round'     => $this->round->value,
                    'can_build' => $this->round->canBeBuiltInAiku(),
                    'sku'   => $this->itemId,
                    'next'  => (int) $versions->max('version') + 1,
                ] : null,
                'lockedReason' => ProductDevelopmentDesignRounds::isUnlocked($productDevelopment, $this->round)
                    ? null
                    : __('Approve the :round first', ['round' => mb_strtolower(ProductDevelopmentDesignRoundEnum::labels()[$this->round->previous()?->value ?? ''] ?? '')]),
                'developmentRoute' => ['name' => 'grp.product_developments.show', 'parameters' => ['productDevelopment' => $productDevelopment->slug, 'tab' => 'design']],
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function version(ProductDevelopmentDesignRound $designRound, User $user, bool $canDesign, bool $canApprove): array
    {
        $productDevelopment = $designRound->productDevelopment;
        $inReview           = $designRound->status === ProductDevelopmentDesignRoundStatusEnum::IN_REVIEW;
        $canConfirmPrint    = $this->productDevelopmentAccess()->canConfirmPrintLimits($user, $productDevelopment);

        return [
            'id'          => $designRound->id,
            'version'     => $designRound->version,
            'status'      => $this->status($designRound),
            'notes'       => $designRound->notes,
            ...self::content($designRound),
            'approved_by' => $this->name($designRound->approvedBy),
            'approved_at' => $designRound->approved_at?->toIso8601String(),
            'can_edit'    => $canDesign && $designRound->status === ProductDevelopmentDesignRoundStatusEnum::DRAFT,
            'can_review'  => $canApprove && $inReview,
            'can_comment' => true,
            'checks'      => collect(ProductDevelopmentDesignRounds::checks())->map(fn (array $check, string $key) => [
                'key'       => $key,
                'label'     => $check['label'],
                'ticked_by' => Arr::get($designRound->data, "checks.$key.name"),
                'ticked_at' => Arr::get($designRound->data, "checks.$key.at"),
                'can_tick'  => $inReview && ($check['by'] === 'agent' ? $canConfirmPrint : $canApprove),
            ])->values()->all(),
            'all_checked' => ProductDevelopmentDesignRounds::allChecksTicked($designRound),
            'next_round'  => $designRound->round->next() ? ProductDevelopmentDesignRoundEnum::labels()[$designRound->round->next()->value] : null,
            'routes'      => [
                'update' => ['name' => 'grp.models.product_development.design.update', 'parameters' => ['productDevelopmentDesignRound' => $designRound->id]],
                'artwork' => ['name' => 'grp.models.product_development.design.artwork', 'parameters' => ['productDevelopmentDesignRound' => $designRound->id]],
                'image'  => ['name' => 'grp.models.product_development.design.image', 'parameters' => ['productDevelopmentDesignRound' => $designRound->id]],
                'submit' => ['name' => 'grp.models.product_development.design.submit', 'parameters' => ['productDevelopmentDesignRound' => $designRound->id]],
                'review' => ['name' => 'grp.models.product_development.design.review', 'parameters' => ['productDevelopmentDesignRound' => $designRound->id]],
            ],
        ];
    }

    /**
     * What a version shows: its box or page built in Aiku, or its uploaded PDF or picture.
     *
     * @return array<string, mixed>
     */
    public static function content(ProductDevelopmentDesignRound $designRound): array
    {
        $source = $designRound->data['source'] ?? StoreProductDevelopmentDesignVersion::SOURCE_EDITOR;
        if ($source === StoreProductDevelopmentDesignVersion::SOURCE_EDITOR) {
            return ['source' => $source, 'layout' => ProductDevelopmentDesignLayout::normalise($designRound->data['layout'] ?? []), 'upload' => null];
        }

        $upload = $designRound->data['upload'] ?? null;
        $media  = $upload ? $designRound->productDevelopment->files()->with('media')->find($upload['file_id'])?->media : null;

        return [
            'source' => $source,
            'layout' => null,
            'upload' => $media ? [
                'kind'        => $upload['kind'],
                'name'        => $upload['name'] ?? $media->file_name,
                'url'         => route('grp.media.download', ['media' => $media->ulid]),
                'preview_url' => route('grp.media.download', ['media' => $media->ulid, 'inline' => 1]),
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function skipState(ProductDevelopment $productDevelopment, User $user): ?array
    {
        if (!$this->round->canBeSkipped()) {
            return null;
        }

        $skipped = $productDevelopment->data['skipped_rounds'][$this->round->value] ?? null;
        $canEdit = $this->productDevelopmentAccess()->canEdit($user, $productDevelopment) && !ProductDevelopmentDesignRounds::isArtworkLocked($productDevelopment);

        return [
            'route'    => ['name' => 'grp.models.product_development.design.skip', 'parameters' => ['productDevelopment' => $productDevelopment->id]],
            'skipped'  => $skipped ? ['by' => $skipped['name'] ?? null, 'at' => $skipped['at'] ?? null] : null,
            'can_skip' => $canEdit && !$skipped && !ProductDevelopmentDesignRounds::isApproved($productDevelopment, $this->round),
            'can_undo' => $canEdit && $skipped && !($this->round->next() && ProductDevelopmentDesignRounds::isApproved($productDevelopment, $this->round->next())),
        ];
    }

    /**
     * @return array{value: string, label: string, color: string, icon: string}|null
     */
    private function status(?ProductDevelopmentDesignRound $designRound): ?array
    {
        if (!$designRound) {
            return null;
        }

        return [
            'value' => $designRound->status->value,
            'label' => ProductDevelopmentDesignRoundStatusEnum::labels()[$designRound->status->value],
            'color' => $designRound->status->color(),
            'icon'  => $designRound->status->icon(),
        ];
    }

    private function name(?User $user): ?string
    {
        return $user ? ($user->contact_name ?: $user->username) : null;
    }

    public function getBreadcrumbs(ProductDevelopment $productDevelopment): array
    {
        return array_merge(
            ShowProductDevelopment::make()->getShowBreadcrumbs($productDevelopment),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.product_developments.design',
                            'parameters' => ['productDevelopment' => $productDevelopment->slug],
                        ],
                        'label' => __('Design'),
                    ],
                ],
            ]
        );
    }
}
