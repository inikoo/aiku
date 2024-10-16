<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:34:03 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Attachment;

use App\Actions\Helpers\Media\StoreMediaFromFile;
use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Media;
use App\Models\HumanResources\Employee;
use App\Models\Ordering\Order;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\StockDelivery;
use App\Models\SupplyChain\Supplier;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveModelAttachment extends OrgAction
{
    use AsAction;

    public function handle(Employee|TradeUnit|Supplier|Customer|PurchaseOrder|StockDelivery|Order $model, array $modelData): Media
    {
        $filePath = Arr::pull($modelData, 'path');

        $checksum = md5_file($filePath);

        /** @var Media $media */
        if (!$media = Media::where('group_id', $model->group_id)->where('type', 'attachment')->where('checksum', $checksum)->first()) {
            $media = StoreMediaFromFile::run(
                $model,
                [
                    'path'         => $filePath,
                    'originalName' => Arr::get($modelData, 'originalName'),
                    'checksum'     => $checksum
                ],
                'attachment',
                'attachment'
            );
            data_forget($modelData, 'last_fetched_at');
        }

        data_forget($modelData, 'originalName');

        $pivotData = array_merge(
            $modelData,
            [
                'group_id' => $model->group_id,
                'data'     => '{}'
            ]
        );

        if ($model->attachments()->where('media_id', $media->id)->exists()) {
            data_forget($modelData, 'fetched_at');
            $model->attachments()->updateExistingPivot($media->id, $pivotData);

            return $media;
        } else {
            data_forget($modelData, 'last_fetched_at');
        }

        $model->attachments()->attach(
            [
                $media->id => $pivotData
            ]
        );

        return $media;
    }

    public function rules(): array
    {
        $rules = [
            'path'         => ['required', 'string'],
            'originalName' => ['required', 'string'],
            'scope'        => ['required', 'string'],
            'sub_scope'    => ['sometimes', 'string'],
            'caption'      => ['sometimes', 'nullable', 'string'],

        ];

        if (!$this->strict) {
            $rules['fetched_at']      = ['sometimes', 'date'];
            $rules['last_fetched_at'] = ['sometimes', 'date'];
            $rules['source_id']       = ['sometimes', 'string', 'max:255'];
        }

        return $rules;
    }

    public function action(Employee|TradeUnit|Supplier|Customer|PurchaseOrder|StockDelivery|Order $model, array $modelData, int $hydratorsDelay = 0, bool $strict = true): Media
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;


        $this->initialisationForGroup($model->group, $modelData);

        return $this->handle($model, $this->validatedData);
    }

}
