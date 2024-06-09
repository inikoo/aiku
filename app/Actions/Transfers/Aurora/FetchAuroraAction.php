<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 00:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Transfers\FetchAction;
use App\Enums\Helpers\Fetch\FetchTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class FetchAuroraAction extends FetchAction
{
    use WithAuroraOrganisationsArgument;


    protected function preProcessCommand(Command $command): void
    {

        if ($command->getName() == 'fetch:webpages') {
            $this->fetchAll = (bool)$command->option('all');
        }


        if (in_array($command->getName(), [
                'fetch:customers',
                'fetch:web-users',
                'fetch:products',
                'fetch:webpages',
                'fetch:invoices',
                'fetch:orders',
                'fetch:delivery-notes',
                'fetch:outers',
                'fetch:products',
                'fetch:services'
            ]) and $command->option('shop')) {
            $this->shop = Shop::where('slug', $command->option('shop'))->firstOrFail();
        }

        if (in_array($command->getName(), [
            'fetch:stocks',
            'fetch:products',
            'fetch:services',
            'fetch:orders',
            'fetch:invoices',
            'fetch:customers',
            'fetch:customer-clients',
            'fetch:delivery-notes',
            'fetch:purchase-orders',
            'fetch:suppliers',
            'fetch:web-users',
            'fetch:prospects',
            'fetch:deleted-customers',
            'fetch:webpages',
            'fetch:supplier-products',
            'fetch:payments',
            'fetch:pallets',
            'fetch:families',
            'fetch:outers',
        ])) {
            $this->onlyNew = (bool)$command->option('only_new');
        }


        if (in_array($command->getName(), [
            'fetch:customers',
            'fetch:orders',
            'fetch:invoices',
            'fetch:delivery-notes',
            'fetch:employees',
            'fetch:stocks',
            'fetch:purchase-orders',
            'fetch:stock-deliveries',
            'fetch:suppliers',

        ])) {
            $this->with = $command->option('with');
        }

    }

    protected function doReset(Command $command): void
    {
        if (in_array($command->getName(), [
                'fetch:stocks',
                'fetch:products',
                'fetch:orders',
                'fetch:invoices',
                'fetch:customers',
                'fetch:customers-clients',
                'fetch:web-users',
                'fetch:delivery-notes',
                'fetch:purchase-orders',
                'fetch:web-users',
                'fetch:prospects',
                'fetch:deleted-customers',
                'fetch:webpages'
            ]) and $command->option('reset')) {
            $this->reset();
        }

    }

    public function getDBPrefix(Command $command): string
    {
        return    $command->option('db_suffix') ?? '';
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($request->user()->userable_type == 'Organisation') {
            $organisation = $request->user()->organisation;

            if ($organisation->id and $request->user()->tokenCan('aurora')) {
                return true;
            }
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'id' => ['sometimes'],
        ];
    }


    /**
     * @throws \Exception
     */
    public function asController(Organisation $organisation, ActionRequest $request): ?Model
    {
        $validatedData = $request->validated();


        $this->organisationSource = $this->getOrganisationSource($organisation);
        $this->organisationSource->initialisation($organisation);

        return $this->handle($this->organisationSource, Arr::get($validatedData, 'id'));
    }

    public function jsonResponse($model): array
    {
        if ($model) {
            return [
                'model'     => $model->getMorphClass(),
                'id'        => $model->id,
                'source_id' => $model->source_id,
            ];
        } else {
            return [
                'error' => 'model not returned'
            ];
        }
    }

    protected function getFetchType(Command $command): FetchTypeEnum
    {
        return match ($command->getName()) {
            'fetch:prospects'           => FetchTypeEnum::PROSPECTS,
            'fetch:invoices'            => FetchTypeEnum::INVOICES,
            'fetch:locations'           => FetchTypeEnum::LOCATIONS,
            'fetch:stocks'              => FetchTypeEnum::STOCKS,
            'fetch:customers'           => FetchTypeEnum::CUSTOMERS,
            'fetch:employees'           => FetchTypeEnum::EMPLOYEES,
            'fetch:supplier-products'   => FetchTypeEnum::SUPPLIER_PRODUCTS,
            default                     => FetchTypeEnum::BASE,
        };
    }


}
