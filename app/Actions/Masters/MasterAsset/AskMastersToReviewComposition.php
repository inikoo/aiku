<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 22:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Masters\MasterAsset;

use App\Actions\Chat\Staff\SendStaffMessage;
use App\Actions\Tasks\StoreStaffTask;
use App\Models\Goods\Stock;
use App\Models\Masters\MasterAsset;
use App\Models\SysAdmin\User;
use App\Models\Tasks\StaffTask;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * A SKU composition change cascades to every partner's SKO, but the masters selling its trade
 * units keep their own composition. The masters department gets one open task per master shop
 * with a sub task per master product, and later changes pile onto that task.
 */
class AskMastersToReviewComposition
{
    use AsAction;

    public const string TASK_KIND = 'master_composition_review';

    public function handle(Stock $stock, User $requester): void
    {
        $stock->unsetRelation('tradeUnits');
        $packing = $stock->tradeUnits->map(fn ($tradeUnit) => (float) $tradeUnit->pivot->quantity.' × '.$tradeUnit->code)->implode(' + ');

        MasterAsset::query()
            ->where('status', true)
            ->whereHas('tradeUnits', fn ($query) => $query->whereIn('trade_units.id', $stock->tradeUnits->pluck('id')))
            ->with(['masterShop', 'tradeUnits'])
            ->orderBy('code')
            ->get()
            ->groupBy('master_shop_id')
            ->each(fn (Collection $masterAssets) => $this->askMasterShop($masterAssets, $stock, $packing, $requester));
    }

    /**
     * @param Collection<int, MasterAsset> $masterAssets
     */
    private function askMasterShop(Collection $masterAssets, Stock $stock, string $packing, User $requester): void
    {
        $masterShop = $masterAssets->first()->masterShop;
        $subtasks   = $masterAssets->map(fn (MasterAsset $masterAsset) => [
            'title'  => mb_substr(__(':code sells :composition, SKU :stock now packs :packing', [
                'code'        => $masterAsset->code,
                'composition' => $masterAsset->tradeUnits->map(fn ($tradeUnit) => (float) $tradeUnit->pivot->quantity.' × '.$tradeUnit->code)->implode(' + '),
                'stock'       => $stock->code,
                'packing'     => $packing,
            ]), 0, 255),
            'status' => 'todo',
        ])->values();
        $links = $masterAssets->map(fn (MasterAsset $masterAsset) => $masterAsset->code.': '.route('grp.masters.master_shops.show.master_products.composition', [
            'masterShop'    => $masterShop->slug,
            'masterProduct' => $masterAsset->slug,
        ]))->implode("\n");

        $openTask = StaffTask::open()
            ->where('data->kind', self::TASK_KIND)
            ->where('data->master_shop_id', $masterShop->id)
            ->first();

        if ($openTask) {
            DB::transaction(function () use ($openTask, $subtasks, $links) {
                $lockedTask = StaffTask::query()->whereKey($openTask->id)->lockForUpdate()->firstOrFail();
                $lockedTask->update([
                    'description' => mb_substr($lockedTask->description."\n".$links, 0, 5000),
                    'data'        => [
                        ...$lockedTask->data,
                        'subtasks' => [...($lockedTask->data['subtasks'] ?? []), ...$subtasks->all()],
                    ],
                ]);
            });
            SendStaffMessage::run($openTask->conversation, $requester, ['body' => __('The composition of SKU :stock changed:', ['stock' => $stock->code])."\n".$links]);

            return;
        }

        StoreStaffTask::make()->action($requester, [
            'subject'     => __('Check master compositions in :shop, SKU packing changed', ['shop' => $masterShop->name]),
            'description' => mb_substr(__(':user changed the composition of SKU :stock, every partner now packs it as :packing. Check these master products still sell the right composition:', [
                'user'    => $requester->chatName(),
                'stock'   => $stock->code,
                'packing' => $packing,
            ])."\n".$links, 0, 5000),
            'department'  => 'masters',
            'data'        => ['kind' => self::TASK_KIND, 'master_shop_id' => $masterShop->id],
            'subtasks'    => $subtasks->all(),
        ]);
    }
}
