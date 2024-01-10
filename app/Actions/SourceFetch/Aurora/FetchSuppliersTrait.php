<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:26:55 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\Supplier\UpdateSupplier;
use App\Models\Procurement\Supplier;
use App\Services\Organisation\SourceOrganisationService;

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


        if (Supplier::withTrashed()->where('source_slug', $supplierData['supplier']['source_slug'])->exists()) {
            if ($supplier = Supplier::withTrashed()->where('source_id', $supplierData['supplier']['source_id'])->first()) {
                $supplier = UpdateSupplier::make()->run($supplier, $supplierData['supplier']);
            }
        } else {
            $supplier = StoreSupplier::run(
                parent: $supplierData['parent'],
                modelData: $supplierData['supplier'],
            );
        }


        if ($supplier) {
            if (array_key_exists('photo', $supplierData)) {
                foreach ($supplierData['photo'] as $photoData) {
                    $this->saveImage($supplier, $photoData);
                }
            }
        }

        return $supplier;
    }


}
