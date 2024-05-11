<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:26:02 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Imports\CRM;

use App\Actions\CRM\Prospect\StoreProspect;
use App\Imports\WithImport;
use App\Models\Helpers\Upload;
use App\Models\Catalogue\Shop;
use App\Rules\Phone;
use Exception;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProspectImport implements ToCollection, WithHeadingRow, SkipsOnFailure, WithValidation, WithEvents
{
    use WithImport;

    protected Shop $scope;
    public function __construct(Shop $scope, Upload $upload)
    {
        $this->scope  = $scope;
        $this->upload = $upload;
    }


    public function storeModel($row, $uploadRecord): void
    {

        $fields =
            array_merge(
                Arr::except(
                    array_keys($this->rules()),
                    []
                ),
                [
                ]
            );


        try {
            $modelData = $row->only($fields)->all();
            data_set($modelData, 'phone', null, overwrite: false);
            data_set($modelData, 'contact_website', null, overwrite: false);

            data_set($modelData, 'data.bulk_import', [
                'id'   => $this->upload->id,
                'type' => 'Upload',
            ]);

            StoreProspect::make()->action(
                $this->scope,
                $modelData,
                1
            );



            $this->setRecordAsCompleted($uploadRecord);
        } catch (Exception $e) {
            $this->setRecordAsFailed($uploadRecord, [$e->getMessage()]);
        }
    }

    public function prepareForValidation($data)
    {

        $tags = explode(',', Arr::get($data, 'tags'));

        if($tags[0] != '') {
            $data['tags'] = $tags;
        } else {
            $data['tags'] = null;
        }

        return $data;
    }



    public function rules(): array
    {


        return [
            'contact_name'    => ['nullable', 'nullable', 'string', 'max:255'],
            'company_name'    => ['nullable', 'nullable', 'string', 'max:255'],
            'email'           => [
                'present','nullable',
                'email',
                'max:500',


            ],
            'phone'           => [
                'nullable',
                new Phone(),
            ],
            'contact_website' => ['nullable'],
            'tags'            => ['nullable', 'array'],
            'tags.*'          => ['nullable', 'string'],
        ];
    }
}
