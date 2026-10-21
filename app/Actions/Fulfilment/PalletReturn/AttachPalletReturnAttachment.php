<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Helpers\Media\SaveModelAttachment;
use App\Enums\Fulfilment\PalletReturn\PalletReturnAttachmentScopeEnum;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Helpers\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsObject;

class AttachPalletReturnAttachment
{
    use AsObject;

    /**
     * A return carries one shipping label, so a new label replaces the one already attached.
     */
    public function handle(PalletReturn $palletReturn, UploadedFile $file, PalletReturnAttachmentScopeEnum $scope, ?string $caption = null): Media
    {
        return DB::transaction(function () use ($palletReturn, $file, $scope, $caption) {
            if ($scope === PalletReturnAttachmentScopeEnum::SHIPPING_LABEL) {
                $palletReturn->attachments()
                    ->wherePivot('scope', PalletReturnAttachmentScopeEnum::SHIPPING_LABEL->value)
                    ->detach();
            }

            return SaveModelAttachment::make()->action($palletReturn, [
                'path'         => $file->getPathname(),
                'originalName' => $file->getClientOriginalName(),
                'scope'        => $scope->value,
                'caption'      => $caption ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'extension'    => $file->getClientOriginalExtension(),
            ]);
        });
    }

    /**
     * @return array<int, mixed>
     */
    public static function fileRules(): array
    {
        return [
            'required',
            'file',
            'max:50000',
            Rule::when(
                fn (Fluent $input) => PalletReturnAttachmentScopeEnum::tryFrom((string) $input->get('scope'))?->isPdfOnly() ?? false,
                ['mimes:pdf']
            ),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public static function scopeRules(): array
    {
        return ['required', Rule::enum(PalletReturnAttachmentScopeEnum::class)];
    }
}
