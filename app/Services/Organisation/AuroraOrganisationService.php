<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 02:46:11 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Services\Organisation;


use App\Models\Assets\Language;
use App\Models\Organisations\Organisation;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AuroraOrganisationService implements SourceOrganisationService
{


    /**
     * @var \App\Models\Organisations\Organisation
     */
    private Organisation $organisation;

    public function fetchUser($id): array|null
    {
        $userData = null;
        if (!$id) {
            return null;
        }


        if ($auData = DB::connection('aurora')->table('User Dimension')
            ->where('User Key', $id)->first()) {
            $userData         = [];
            $userData['user'] = [
                'status'      => $auData->{'User Active'} == 'Yes',
                'language_id' => $this->parseLanguageID($auData->{'User Preferred Locale'}),
            ];

            $profile_images=collect();
            if ($auData->{'User Type'} == 'Staff') {
                $profile_images=$this->getModelImagesCollection(
                    'Staff',
                    $auData->{'User Parent Key'}
                )->map(function ($auroraImage) use ($profile_images) {
                    return $this->fetchImage($auroraImage);
                });
            }
            $userData['profile_images'] =$profile_images->toArray();
        }

        return $userData;
    }

    public function initialisation(Organisation $organisation)
    {
        $database_settings = data_get(config('database.connections'), 'aurora');
        data_set($database_settings, 'database', $organisation->data['db_name']);
        config(['database.connections.aurora' => $database_settings]);
        DB::connection('aurora');
        DB::purge('aurora');

        $this->organisation = $organisation;
    }

    protected function parseLanguageID($locale): int|null
    {
        if ($locale != '') {
            try {
                return Language::where(
                    'code',
                    match ($locale) {
                        'zh_CN.UTF-8' => 'zh-CN',
                        default => substr($locale, 0, 2)
                    }
                )->first()->id;
            } catch (Exception) {
                //print "Locale $locale not found\n";

                return null;
            }
        }

        return null;
    }

    protected function getModelImagesCollection($model, $id): Collection
    {
        return DB::connection('aurora')
            ->table('Image Subject Bridge')
            ->leftJoin('Image Dimension', 'Image Subject Image Key', '=', 'Image Key')
            ->where('Image Subject Object', $model)
            ->where('Image Subject Object Key', $id)
            ->orderByRaw("FIELD(`Image Subject Is Principal`, 'Yes','No')")
            ->get();
    }

    protected function fetchImage($auroraImageData): array
    {
        $image_path = sprintf(
            config('app.aurora_image_path'),
            Arr::get($this->organisation->data, 'account_code')
        );


        $image_path .= '/'
            .$auroraImageData->{'Image File Checksum'}[0].'/'
            .$auroraImageData->{'Image File Checksum'}[1].'/'
            .$auroraImageData->{'Image File Checksum'}.'.'
            .$auroraImageData->{'Image File Format'};



        if (file_exists($image_path)) {
            return [
                'image_path' => $image_path,
                'filename'   => $auroraImageData->{'Image Filename'},
                'mime'       => $auroraImageData->{'Image MIME Type'},

            ];
        } else {
            return [];
        }
    }
}
