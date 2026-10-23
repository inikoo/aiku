<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem;

use App\Actions\Helpers\Media\SaveModelAttachment;
use App\Enums\Fulfilment\StoredItem\StoredItemAttachmentScopeEnum;
use App\Models\Fulfilment\StoredItem;
use App\Models\Helpers\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsObject;

class AttachStoredItemManual
{
    use AsObject;

    public function handle(StoredItem $storedItem, UploadedFile $file, ?string $caption = null): Media
    {
        return SaveModelAttachment::make()->action($storedItem, [
            'path'         => $file->getPathname(),
            'originalName' => $file->getClientOriginalName(),
            'scope'        => StoredItemAttachmentScopeEnum::MANUAL->value,
            'caption'      => $caption ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'extension'    => $file->getClientOriginalExtension(),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'attachments'   => ['required', 'array'],
            'attachments.*' => ['required', 'file', 'max:50000', 'mimes:pdf'],
            'scope'         => ['required', Rule::enum(StoredItemAttachmentScopeEnum::class)],
            'caption'       => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
