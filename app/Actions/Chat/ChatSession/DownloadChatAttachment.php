<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Jun 2026 21:08:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Chat\ChatSession;

use App\Models\Helpers\Media;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpFoundation\HeaderUtils;

class DownloadChatAttachment
{
    use AsAction;

    /**
     * Inline serving backs the audio player in the chat bubble: a media element needs
     * a streamable response (byte ranges, inline disposition) rather than a download.
     */
    public function handle(string $ulid, bool $inline = false)
    {

        $media = Media::where('ulid', $ulid)->firstOrFail();


        /* A template message points at the template's own header media rather than
           copying it per send, so that media has to be servable here too — otherwise
           the video or document in the bubble can be neither played nor downloaded. */
        $allowedModels = [
            'App\Models\Chat\ChatMessage',
            'App\Models\Chat\MetaChatMessage',
            'App\Models\Chat\MetaMessageTemplate',
        ];

        if (!in_array($media->model_type, $allowedModels, true)) {
            abort(403);
        }

        $headers = [
            'Content-Type'        => $media->mime_type,
            'Content-Disposition' => HeaderUtils::makeDisposition($inline ? 'inline' : 'attachment', $this->fileName($media), Str::ascii($media->file_name)),
        ];

        if ($media->getCustomProperty('archived_at')) {
            return response(GetChatMediaContents::run($media), 200, $headers);
        }

        return response()->file($media->getPath(), $headers);
    }

    public function fileName(Media $media): string
    {
        $name      = str_replace(['/', '\\'], '-', $media->name ?: $media->file_name);
        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);

        if ($extension && !str_ends_with(strtolower($name), '.'.strtolower($extension))) {
            $name .= '.'.$extension;
        }

        return $name;
    }

    public function asController(string $ulid, ActionRequest $request)
    {
        return $this->handle($ulid, $request->boolean('inline'));
    }
}
