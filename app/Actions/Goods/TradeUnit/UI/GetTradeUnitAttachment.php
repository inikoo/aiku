<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 27-05-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Goods\TradeUnit\UI;

use App\Actions\Traits\HasBucketAttachment;
use App\Enums\Goods\TradeUnit\TradeUnitStatusEnum;
use App\Models\Goods\TradeUnit;
use Lorisleiva\Actions\Concerns\AsObject;
use App\Http\Resources\Helpers\Attachment\AttachmentsResource;
use App\Actions\Helpers\Media\UI\IndexAttachments;
use Illuminate\Support\Str;

class GetTradeUnitAttachment
{
    use AsObject;
    use HasBucketAttachment;

    public function handle(TradeUnit $tradeUnit): array
    {
        return [
            'id'                        => $tradeUnit->id,
            'editable'                  => true,
            'attachment_category_box'   => $this->getAttachmentData($tradeUnit),
            'attachRoute' => [
                'name'       => 'grp.models.trade-unit.attachment.attach',
                'parameters' => [
                    'tradeUnit' => $tradeUnit->id,
                ]
            ],
            'detachRoute' => [
                'name'       => 'grp.models.trade-unit.attachment.detach',
                'parameters' => [
                    'tradeUnit' => $tradeUnit->id,
                ],
                'method'     => 'delete'
            ],
            'attachments'   => AttachmentsResource::collection(IndexAttachments::run($tradeUnit))->resolve(),
            'family_documents' => GetTradeUnitDocuments::make()->forTradeUnitFamilyOf($tradeUnit),
            'documents_sharing' => $this->documentsSharing($tradeUnit),
        ];
    }

    private function documentsSharing(TradeUnit $tradeUnit): array
    {
        $documentsTradeUnit = $tradeUnit->documentsTradeUnit;

        return [
            'source'      => $documentsTradeUnit ? $this->tradeUnitLink($documentsTradeUnit) : null,
            'sharers'     => $tradeUnit->documentsSharers()->orderBy('code')->get()->map(fn (TradeUnit $sharer) => $this->tradeUnitLink($sharer))->all(),
            'suggestions' => $documentsTradeUnit ? [] : $this->suggestions($tradeUnit),
            'shareRoute'  => [
                'name'       => 'grp.models.trade-unit.share_documents',
                'parameters' => ['tradeUnit' => $tradeUnit->id],
                'method'     => 'post',
            ],
            'stopSharingRoute' => [
                'name'       => 'grp.models.trade-unit.stop_sharing_documents',
                'parameters' => ['tradeUnit' => $tradeUnit->id],
                'method'     => 'delete',
            ],
        ];
    }

    /**
     * Other sizes of the same substance: trade units whose code ends with the same "-number".
     * Ticked when its code prefix already shares from a trade unit with this one's prefix (e.g. EO-02 from EOKG-02 once EO-01 shares from EOKG-01).
     *
     * @return list<array{id: int, code: string, name: string|null, checked: bool}>
     */
    private function suggestions(TradeUnit $tradeUnit): array
    {
        if (!str_contains($tradeUnit->code, '-')) {
            return [];
        }

        $prefix = Str::beforeLast($tradeUnit->code, '-');
        $suffix = Str::afterLast($tradeUnit->code, '-');
        if ($suffix === '') {
            return [];
        }

        $sharingPrefixes = TradeUnit::where('group_id', $tradeUnit->group_id)->whereHas('documentsTradeUnit', fn ($query) => $query->whereLike('code', $this->escapeLike($prefix).'-%', caseSensitive: false))
            ->pluck('code')
            ->map(fn (string $code) => Str::lower(Str::beforeLast($code, '-')))
            ->unique()
            ->all();

        return TradeUnit::where('group_id', $tradeUnit->group_id)
            ->whereLike('code', '%-'.$this->escapeLike($suffix), caseSensitive: false)
            ->whereNot('id', $tradeUnit->id)
            ->whereNull('documents_trade_unit_id')
            ->whereDoesntHave('documentsSharers')
            ->where('status', TradeUnitStatusEnum::ACTIVE)
            ->orderBy('code')
            ->limit(30)
            ->get(['id', 'code', 'name'])
            ->map(fn (TradeUnit $suggestion) => [
                'id'      => $suggestion->id,
                'code'    => $suggestion->code,
                'name'    => $suggestion->name,
                'checked' => in_array(Str::lower(Str::beforeLast($suggestion->code, '-')), $sharingPrefixes),
            ])
            ->all();
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }

    private function tradeUnitLink(TradeUnit $tradeUnit): array
    {
        return [
            'id'    => $tradeUnit->id,
            'code'  => $tradeUnit->code,
            'name'  => $tradeUnit->name,
            'route' => [
                'name'       => 'grp.trade_units.units.show',
                'parameters' => ['tradeUnit' => $tradeUnit->slug, 'tab' => 'attachments'],
            ],
        ];
    }
}
