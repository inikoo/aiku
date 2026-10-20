<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\SocialMedia;

use App\Actions\Helpers\Upload\ImportUpload;
use App\Actions\Helpers\Upload\StoreUpload;
use App\Actions\Traits\WithImportModel;
use App\Http\Resources\Helpers\UploadsResource;
use App\Imports\CRM\SocialMediaInsightsImport;
use App\Models\Catalogue\Shop;
use App\Models\CRM\SocialMediaPost;
use App\Models\Helpers\Upload;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class ImportSocialMediaInsights
{
    use WithImportModel;

    public function handle(Shop $shop, $file): Upload
    {
        $upload = StoreUpload::make()->fromFile(
            $shop->group,
            $file,
            [
                'model'       => class_basename(SocialMediaPost::class),
                'parent_type' => $shop->getMorphClass(),
                'parent_id'   => $shop->id,
            ]
        );

        if ($this->isSync) {
            ImportUpload::run($file, new SocialMediaInsightsImport($shop, $upload));
            $upload->refresh();
        } else {
            ImportUpload::dispatch($this->tmpPath.$upload->filename, new SocialMediaInsightsImport($shop, $upload));
        }

        return $upload;
    }

    public function authorize(ActionRequest $request): bool
    {
        $shop = $request->route('shop');

        return $shop instanceof Shop && $request->user()->authTo("marketing.$shop->id.edit");
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
        ];
    }

    public function asController(Shop $shop, ActionRequest $request): Upload
    {
        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);

        return $this->handle($shop, $file);
    }

    public function jsonResponse(Upload $upload): array
    {
        return UploadsResource::make($upload)->getArray();
    }

    public string $commandSignature = 'social-media:import {shop} {--g|g_drive} {filename}';

    public function runImportForCommand($file, $command): Upload
    {
        return $this->handle(Shop::where('slug', $command->argument('shop'))->firstOrFail(), $file);
    }
}
