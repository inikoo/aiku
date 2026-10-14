<?php

namespace App\Actions\Fulfilment\Pallet;

use App\Actions\OrgAction;
use App\Actions\Traits\WithExportData;
use App\Enums\UI\Fulfilment\FulfilmentCustomerPalletsTabsEnum;
use App\Exports\Pallets\PalletsInCustomerExport;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportPalletsInCustomer extends OrgAction
{
    use WithExportData {
        rules as exportRules;
    }

    /**
     * @throws \Throwable
     */
    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): BinaryFileResponse
    {
        $type                = $modelData['type'];
        $tab                 = $modelData['tab'] ?? FulfilmentCustomerPalletsTabsEnum::ALL->value;
        $oneRowPerStoredItem = (bool) Arr::get($modelData, 'one_row_per_sko', false);

        return $this->export(
            new PalletsInCustomerExport($fulfilmentCustomer, $tab, $oneRowPerStoredItem),
            $this->filenamePrefix($tab, $oneRowPerStoredItem),
            $type
        );
    }

    public function filenamePrefix(string $tab, bool $oneRowPerStoredItem): string
    {
        return ($oneRowPerStoredItem ? 'customer-sko-' : 'customer-pallets-').$tab;
    }

    public function rules(): array
    {
        return array_merge($this->exportRules(), [
            'tab' => [
                'sometimes',
                Rule::in(FulfilmentCustomerPalletsTabsEnum::values()),
            ],
            'one_row_per_sko' => ['sometimes', 'boolean'],
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): BinaryFileResponse
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        $modelData = $this->validatedData;

        if (!Arr::has($modelData, 'tab')) {
            $query = [];
            parse_str((string) parse_url((string) $request->headers->get('referer'), PHP_URL_QUERY), $query);

            $refererTab = Arr::get($query, 'tab');
            if (is_string($refererTab) && in_array($refererTab, FulfilmentCustomerPalletsTabsEnum::values(), true)) {
                $modelData['tab'] = $refererTab;
            }
        }

        return $this->handle($fulfilmentCustomer, $modelData);
    }
}
