<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect;

use App\Actions\Helpers\Upload\ImportUpload;
use App\Actions\Helpers\Upload\StoreUpload;
use App\Actions\Traits\WithImportModel;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Http\Resources\Helpers\UploadsResource;
use App\Imports\CRM\ProspectImport;
use App\Models\Helpers\Upload;
use App\Models\Catalogue\Shop;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\ActionRequest;

class ImportShopProspects
{
    use WithImportModel;

    public function handle(Shop $scope, $file): Upload
    {
        $upload = StoreUpload::make()->fromFile(
            $scope->group,
            $file,
            [
                'model' => 'Prospect',
                'parent_type' => $scope->getMorphClass(),
                'parent_id' => $scope->id,
            ]
        );


        if ($this->isSync) {
            ImportUpload::run(
                $file,
                new ProspectImport($scope, $upload)
            );
            $upload->refresh();
        } else {
            ImportUpload::dispatch(
                $this->tmpPath.$upload->filename,
                new ProspectImport($scope, $upload)
            );
        }


        return $upload;
    }


    public function authorize(ActionRequest $request): bool
    {
        $shop = $request->route('shop');
        if (!$shop instanceof Shop) {
            return false;
        }

        if ($shop->type == ShopTypeEnum::FULFILMENT) {
            return $request->user()->authTo([
                "fulfilment-shop.{$shop->fulfilment->id}.edit",
                "supervisor-fulfilment-shop.{$shop->fulfilment->id}",
            ]);
        }

        return $request->user()->authTo([
            "crm.{$shop->id}.edit",
            "crm.{$shop->id}.prospects.edit",
        ]);
    }

    public function inShop(Shop $shop, ActionRequest $request): Upload
    {
        abort_unless($this->authorize($request), 403);

        $file = $request->file('file');
        Storage::disk('local')->put($this->tmpPath, $file);
        return $this->handle($shop, $file);
    }

    public function jsonResponse(Upload $upload): array
    {
        return UploadsResource::make($upload)->getArray();
    }

    public string $commandSignature = 'shop:import-prospects {shop} {--g|g_drive} {filename}';

    public function runImportForCommand($file, $command): Upload
    {
        $shop = Shop::where('slug', $command->argument('shop'))->firstOrFail();

        return $this->handle($shop, $file);
    }


}
