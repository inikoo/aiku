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
     * Other sizes of the same substance: trade units whose code ends with the same "-number" and whose name shares a substance word, or whose line already shares from this line, closest first.
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

        $substanceWords = $this->substanceWords($tradeUnit->name);
        if ($substanceWords === [] && $sharingPrefixes === []) {
            return [];
        }
        $isSharingLine = fn (TradeUnit $candidate) => in_array(Str::lower(Str::beforeLast($candidate->code, '-')), $sharingPrefixes);
        $sizeRoot      = $this->sizeRoot($prefix);
        $isTicked      = fn (TradeUnit $candidate) => $isSharingLine($candidate) || $sizeRoot !== '' && $this->sizeRoot(Str::beforeLast($candidate->code, '-')) === $sizeRoot;

        return TradeUnit::where('group_id', $tradeUnit->group_id)
            ->whereLike('code', '%-'.$this->escapeLike($suffix), caseSensitive: false)
            ->where(function ($query) use ($substanceWords, $sharingPrefixes, $suffix) {
                foreach ($substanceWords as $word) {
                    $query->orWhereLike('name', '%'.$this->escapeLike($word).'%', caseSensitive: false);
                }
                foreach ($sharingPrefixes as $sharingPrefix) {
                    $query->orWhereLike('code', $this->escapeLike($sharingPrefix.'-'.$suffix), caseSensitive: false);
                }
            })
            ->whereNot('id', $tradeUnit->id)
            ->whereNull('documents_trade_unit_id')
            ->whereDoesntHave('documentsSharers')
            ->where('status', TradeUnitStatusEnum::ACTIVE)
            ->get(['id', 'code', 'name'])
            ->filter(fn (TradeUnit $candidate) => $isSharingLine($candidate) || $this->prefixesOverlap($prefix, Str::beforeLast($candidate->code, '-')))
            ->sortBy([
                fn (TradeUnit $a, TradeUnit $b) => $isTicked($b) <=> $isTicked($a),
                fn (TradeUnit $a, TradeUnit $b) => $this->sharedWordCount($b->name, $substanceWords) <=> $this->sharedWordCount($a->name, $substanceWords),
                fn (TradeUnit $a, TradeUnit $b) => strcasecmp($a->code, $b->code),
            ])
            ->take(30)
            ->values()
            ->map(fn (TradeUnit $suggestion) => [
                'id'      => $suggestion->id,
                'code'    => $suggestion->code,
                'name'    => $suggestion->name,
                'checked' => $isTicked($suggestion),
            ])
            ->all();
    }

    /**
     * Words of the name that tell the substance apart (Lavender, Tea, Tree), leaving out sizes, packing and generic words.
     *
     * @return list<string>
     */
    private function substanceWords(?string $name): array
    {
        $genericWords = ['essential', 'oil', 'oils', 'bulk', 'unbranded', 'label', 'labelled', 'unlabelled', 'pure', 'dilute', 'diluted', 'absolute', 'and', 'the', 'with', 'for'];

        return collect(preg_split('/[^\p{L}]+/u', Str::lower((string) $name), flags: PREG_SPLIT_NO_EMPTY))
            ->filter(fn (string $word) => mb_strlen($word) >= 3 && !in_array($word, $genericWords))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Code prefixes of sizes of one product line share two letters in a row (EOKG, EO, EOB, PrEO, PrEOUL, EOUL).
     */
    private function prefixesOverlap(string $prefix, string $candidatePrefix): bool
    {
        $prefix          = Str::lower($prefix);
        $candidatePrefix = Str::lower($candidatePrefix);

        for ($position = 0; $position < mb_strlen($prefix) - 1; $position++) {
            if (str_contains($candidatePrefix, mb_substr($prefix, $position, 2))) {
                return true;
            }
        }

        return false;
    }

    /**
     * The line a code prefix belongs to once the size markers are taken off: PrEOUL, EOKG, EOB, EOUL and PrEO are all sizes of EO,
     * while EOSC, OrgEO or EOB5 stay lines of their own.
     */
    private function sizeRoot(string $prefix): string
    {
        return preg_replace(['/^pr/', '/(ul)$/', '/(kg|b)$/'], '', Str::lower($prefix));
    }

    /**
     * @param  list<string>  $substanceWords
     */
    private function sharedWordCount(?string $name, array $substanceWords): int
    {
        return count(array_intersect($substanceWords, $this->substanceWords($name)));
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
