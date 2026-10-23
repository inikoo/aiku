<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\Pdf;

use App\Actions\Helpers\Media\MergePdfs;
use App\Enums\Fulfilment\StoredItem\StoredItemAttachmentScopeEnum;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

trait WithPalletReturnPrintDocuments
{
    /**
     * @param  array<int, string>  $scopes
     * @return array<int, array{name: string, pdf: string}>
     */
    protected function attachmentDocuments(PalletReturn $palletReturn, array $scopes): array
    {
        return $palletReturn->attachments()
            ->wherePivotIn('scope', $scopes)
            ->get()
            ->sortBy(fn (Media $media) => [array_search($media->pivot->scope, $scopes), $media->id])
            ->map(fn (Media $media) => $this->mediaDocument($media))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{name: string, pdf: string}>
     */
    protected function productManualDocuments(PalletReturn $palletReturn): array
    {
        return $this->productManualsQuery($palletReturn)
            ->orderBy('model_has_attachments.model_id')
            ->orderBy('media.id')
            ->get()
            ->unique('id')
            ->map(fn (Media $media) => $this->mediaDocument($media))
            ->values()
            ->all();
    }

    protected function productManualsQuery(PalletReturn $palletReturn): Builder
    {
        return Media::query()
            ->select('media.*')
            ->join('model_has_attachments', 'model_has_attachments.media_id', '=', 'media.id')
            ->where('model_has_attachments.model_type', 'StoredItem')
            ->where('model_has_attachments.scope', StoredItemAttachmentScopeEnum::MANUAL->value)
            ->whereIn('model_has_attachments.model_id', $palletReturn->items()->whereNotNull('stored_item_id')->select('stored_item_id'));
    }

    /**
     * @return array{name: string, pdf: string}
     */
    private function mediaDocument(Media $media): array
    {
        return [
            'name' => $media->file_name,
            'pdf'  => Storage::disk($media->disk)->get($media->getPathRelativeToRoot()),
        ];
    }

    /**
     * @param  array<int, array{name: string, pdf: string}>  $documents
     */
    protected function mergedPdfResponse(array $documents, string $filename, string $nothingToPrintMessage): Response
    {
        if (!$documents) {
            throw ValidationException::withMessages(['messages' => $nothingToPrintMessage]);
        }

        return response(MergePdfs::run($documents), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
    }
}
