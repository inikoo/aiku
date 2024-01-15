<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\SysAdmin\Organisation\AttachImageToOrganisation;
use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Http\Resources\Gallery\ImageResource;
use App\Models\Mail\Mailshot;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

use function Sentry\captureException;

class UploadImagesToMailshot
{
    use AsAction;
    use WithAttributes;


    /**
     * @var \App\Models\Mail\Mailshot
     */
    private Mailshot $mailshot;

    public function handle(Mailshot $mailshot, array $imageFiles): Collection
    {
        $organisation = organisation();

        $medias = [];

        foreach ($imageFiles as $imageFile) {
            $medias[] = AttachImageToOrganisation::run(
                organisation: $organisation,
                collection: 'mail',
                imagePath: $imageFile->getPathName(),
                originalFilename: $imageFile->getClientOriginalName(),
                extension: $imageFile->guessClientExtension()
            );
        }

        return collect($medias);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->mailshot->type == MailshotTypeEnum::PROSPECT_MAILSHOT) {
            return $request->user()->hasPermissionTo("crm.prospects.edit");
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'images'   => ['required'],
            'images.*' => ["mimes:jpg,png,jpeg", "max:102400"]
        ];
    }


    public function asController(Mailshot $mailshot, ActionRequest $request): Collection
    {
        $this->mailshot = $mailshot;

        try {
            $request->validate();
        } catch (Exception $e) {
            captureException($e);
        }

        return $this->handle($mailshot, $request->validated('images'));
    }


    public function jsonResponse($medias): AnonymousResourceCollection
    {
        return ImageResource::collection($medias);
    }

}
