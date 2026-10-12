<?php

namespace App\Actions\HumanResources\EmployeeBulkEmail;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithHumanResourcesAuthorisation;
use App\Models\HumanResources\EmployeeBulkEmail;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadEmployeeBulkEmailAttachment extends OrgAction
{
    use WithHumanResourcesAuthorisation;

    public function handle(EmployeeBulkEmail $employeeBulkEmail, int $attachmentIndex): StreamedResponse
    {
        $attachment = ($employeeBulkEmail->attachments ?? [])[$attachmentIndex] ?? null;

        abort_unless($attachment && Storage::disk('local')->exists($attachment['path']), 404);

        return Storage::disk('local')->download($attachment['path'], $attachment['name']);
    }

    public function asController(Organisation $organisation, EmployeeBulkEmail $employeeBulkEmail, int $attachment, ActionRequest $request): StreamedResponse
    {
        $this->initialisation($organisation, $request);

        abort_unless($employeeBulkEmail->organisation_id === $organisation->id, 404);

        return $this->handle($employeeBulkEmail, $attachment);
    }
}
