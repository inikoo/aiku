<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 15:45
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Actions\Procurement\AgentGoodsReceipt;

use App\Actions\Helpers\Media\SaveModelAttachment;
use App\Actions\OrgAction;
use App\Models\Procurement\AgentGoodsReceipt;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

/**
 * Photos or scans of the supplier's delivery note, signed when the goods were handed over, kept on the goods receipt.
 */
class UploadAgentGoodsReceiptAttachments extends OrgAction
{
    use WithAgentGoodsReceipt;

    public const string SCOPE = 'delivery_note';

    public function handle(AgentGoodsReceipt $receipt, array $modelData): AgentGoodsReceipt
    {
        $this->saveFiles($receipt, $modelData['attachments']);

        return $receipt;
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function saveFiles(AgentGoodsReceipt $receipt, array $files): void
    {
        foreach ($files as $file) {
            SaveModelAttachment::make()->action($receipt, [
                'path'         => $file->getPathName(),
                'originalName' => $file->getClientOriginalName(),
                'extension'    => $file->getClientOriginalExtension(),
                'scope'        => self::SCOPE,
                'caption'      => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'attachments'   => ['required', 'array', 'min:1', 'max:10'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,heic,heif,pdf', 'max:20000'],
        ];
    }

    public function asController(Organisation $organisation, AgentGoodsReceipt $agentGoodsReceipt, ActionRequest $request): AgentGoodsReceipt
    {
        $this->ensureReceiptOfOrganisationAgent($organisation, $agentGoodsReceipt);
        $this->initialisation($organisation, $request);

        return $this->handle($agentGoodsReceipt, $this->validatedData);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::back();
    }
}
