<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-18h-25m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Actions\Goods\ProductDevelopment\UI;

use App\Models\Goods\ProductDevelopmentItem;
use App\Models\Goods\ProductDevelopmentFile;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentFileScopeEnum;
use App\Actions\Traits\Authorisations\WithProductDevelopmentAuthorisation;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\TradeUnit\TradeUnitMarketEnum;
use App\Models\Goods\ProductDevelopment;
use App\Models\Goods\ProductDevelopmentMember;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetProductDevelopmentTabData
{
    use AsAction;
    use WithProductDevelopmentAuthorisation;

    /**
     * @return array<string, mixed>
     */
    public function brief(ProductDevelopment $productDevelopment, ?User $viewer = null): array
    {
        $canOpenProfiles = (bool) $viewer?->authTo('sysadmin.view');
        $roleLabels   = ProductDevelopmentMemberRoleEnum::labels();
        $marketLabels = TradeUnitMarketEnum::labels();

        return [
            'brief'               => $productDevelopment->brief,
            'brief_editor'        => $this->briefEditor($productDevelopment, $viewer),
            'markets'             => collect($productDevelopment->markets)->map(fn (string $market) => $marketLabels[$market] ?? $market)->values()->all(),
            'target_landing_date' => $productDevelopment->target_landing_date?->toDateString(),
            'target_online_date'  => $productDevelopment->target_online_date?->toDateString(),
            'packaging_family'    => $productDevelopment->packagingFamily?->name ?? $productDevelopment->packagingFamily?->code,
            'members'             => $productDevelopment->members
                ->load('user')
                ->sortBy(fn (ProductDevelopmentMember $member) => array_search($member->role, ProductDevelopmentMemberRoleEnum::cases(), true))
                ->map(fn (ProductDevelopmentMember $member) => [
                    'id'          => $member->id,
                    'user_id'     => $member->user_id,
                    'name'        => $member->user?->contact_name ?: $member->user?->username,
                    'username'    => $member->user?->username,
                    'avatar'      => $member->user?->image_id ? $member->user->imageSources(48, 48) : null,
                    'profile_url' => $canOpenProfiles && $member->user ? route('grp.sysadmin.users.show', ['user' => $member->user->slug]) : null,
                    'role'   => [
                        'label' => $roleLabels[$member->role->value],
                        'icon'  => $member->role->icon(),
                        'color' => $member->role->color(),
                    ],
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * The general brief's PDF references and each SKU's own brief and reference images, with what the viewer may change.
     *
     * @return array<string, mixed>
     */
    private function briefEditor(ProductDevelopment $productDevelopment, ?User $viewer): array
    {
        $canEdit   = $viewer && $this->canEditProductDevelopment($viewer, $productDevelopment);
        $canUpload = $viewer && $this->productDevelopmentAccess()->canUpload($viewer, $productDevelopment);
        $files     = $productDevelopment->files()
            ->whereIn('scope', [ProductDevelopmentFileScopeEnum::BRIEF_REFERENCE->value, ProductDevelopmentFileScopeEnum::BRIEF_IMAGE->value])
            ->whereNull('superseded_at')
            ->with('media')
            ->orderBy('id')
            ->get();
        $fileRow   = fn (ProductDevelopmentFile $file) => [
            'id'           => $file->id,
            'name'         => $file->media?->name,
            'mime'         => $file->media?->mime_type,
            'size'         => $file->media?->size,
            'url'          => $file->media ? route('grp.media.download', ['media' => $file->media->ulid]) : null,
            'preview_url'  => $file->media ? route('grp.media.download', ['media' => $file->media->ulid, 'inline' => 1]) : null,
            'remove_route' => $canUpload ? ['name' => 'grp.models.product_development.brief_file.remove', 'parameters' => ['productDevelopmentFile' => $file->id]] : null,
        ];

        return [
            'can_edit'   => $canEdit,
            'can_upload' => $canUpload,
            'references' => $files->where('scope', ProductDevelopmentFileScopeEnum::BRIEF_REFERENCE)->map($fileRow)->values()->all(),
            'products'   => $productDevelopment->items()->with('tradeUnit')->get()->map(fn (ProductDevelopmentItem $item) => [
                'id'          => $item->id,
                'code'        => $item->tradeUnit->code,
                'name'        => $item->tradeUnit->name,
                'image'       => $item->tradeUnit->image_id ? $item->tradeUnit->imageSources(96, 96) : null,
                'brief'       => $item->brief,
                'images'      => $files->where('scope', ProductDevelopmentFileScopeEnum::BRIEF_IMAGE)->where('product_development_item_id', $item->id)->map($fileRow)->values()->all(),
                'brief_route' => $canEdit ? ['name' => 'grp.models.product_development.item.brief', 'parameters' => ['productDevelopmentItem' => $item->id]] : null,
            ])->values()->all(),
            'routes'     => [
                'update' => $canEdit ? ['name' => 'grp.models.product_development.update', 'parameters' => ['productDevelopment' => $productDevelopment->id]] : null,
                'upload' => $canUpload ? ['name' => 'grp.models.product_development.files.upload', 'parameters' => ['productDevelopment' => $productDevelopment->id]] : null,
            ],
        ];
    }
}
