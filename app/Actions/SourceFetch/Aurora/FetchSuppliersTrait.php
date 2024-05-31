<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:26:55 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Studio\Attachment\SaveModelAttachment;
use App\Actions\Studio\Media\SaveModelImage;
use App\Actions\Procurement\OrgSupplier\StoreOrgSupplier;
use App\Actions\SupplyChain\Supplier\StoreSupplier;
use App\Actions\SupplyChain\Supplier\UpdateSupplier;
use App\Models\Procurement\OrgSupplier;
use App\Models\SupplyChain\Supplier;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Arr;

trait FetchSuppliersTrait
{
    /**
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist
     * @throws \Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig
     */
    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Supplier
    {
        $supplierData = $this->fetch($organisationSource, $organisationSourceId);
        if (!$supplierData) {
            return null;
        }

        $baseSupplier = null;

        if (Supplier::withTrashed()->where('source_slug', $supplierData['supplier']['source_slug'])->exists()) {
            if ($supplier = Supplier::withTrashed()->where('source_id', $supplierData['supplier']['source_id'])->first()) {
                $supplier = UpdateSupplier::make()->run($supplier, $supplierData['supplier']);
            }
            $baseSupplier = Supplier::withTrashed()->where('source_slug', $supplierData['supplier']['source_slug'])->first();
        } else {
            $supplier = StoreSupplier::run(
                parent: $supplierData['parent'],
                modelData: $supplierData['supplier'],
            );
            $supplier->refresh();

            foreach (Arr::get($supplierData, 'photo', []) as $photoData) {
                if (isset($photoData['image_path']) and isset($photoData['filename'])) {
                    SaveModelImage::run(
                        $supplier,
                        [
                            'path'         => $photoData['image_path'],
                            'originalName' => $photoData['filename'],

                        ],
                        'photo'
                    );
                }
            }

        }
        $organisation = $organisationSource->getOrganisation();

        if ($supplier) {
            $orgSupplier = OrgSupplier::where('organisation_id', $organisation->id)->where('supplier_id', $supplier->id)->first();
            if ($orgSupplier) {
                return $supplier;
            }

            if ($supplier->agent_id) {
                OrgSupplier::where('supplier_id', $supplier->id)
                    ->where('organisation_id', $organisationSource->getOrganisation()->id)
                    ->update(
                        [
                            'source_id' => $supplierData['supplier']['source_id']
                        ]
                    );
            } else {
                StoreOrgSupplier::make()->run(
                    $organisationSource->getOrganisation(),
                    $supplier,
                    [
                        'source_id' => $supplierData['supplier']['source_id']
                    ]
                );
            }


        } elseif ($baseSupplier) {
            $orgSupplier = OrgSupplier::where('organisation_id', $organisation->id)->where('supplier_id', $baseSupplier->id)->first();
            if ($orgSupplier) {
                return $supplier;
            }

            StoreOrgSupplier::make()->run(
                $organisationSource->getOrganisation(),
                $baseSupplier,
                [
                    'source_id' => $supplierData['supplier']['source_id']
                ]
            );
        }

        if (in_array('attachments', $this->with)) {
            $sourceData= explode(':', $supplier->source_id);
            foreach ($this->parseAttachments($sourceData[1]) ?? [] as $attachmentData) {

                SaveModelAttachment::run(
                    $supplier,
                    $attachmentData['fileData'],
                    $attachmentData['modelData'],
                );
                $attachmentData['temporaryDirectory']->delete();
            }
        }


        return $supplier;
    }

    private function parseAttachments($staffKey): array
    {
        $attachments            = $this->getModelAttachmentsCollection(
            'Supplier',
            $staffKey
        )->map(function ($auroraAttachment) {return $this->fetchAttachment($auroraAttachment);});
        return $attachments->toArray();
    }


}
