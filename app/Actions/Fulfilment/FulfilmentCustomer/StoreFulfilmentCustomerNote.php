<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 03-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Fulfilment\FulfilmentCustomer;

use App\Actions\OrgAction;
use App\Enums\Helpers\Audit\AuditEventEnum;
use App\Enums\UI\Fulfilment\FulfilmentCustomerTabsEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Helpers\History;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Symfony\Component\HttpFoundation\Response;

class StoreFulfilmentCustomerNote extends OrgAction
{
    /**
     * @throws \Throwable
     */
    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): History
    {
        data_set($modelData, 'event', AuditEventEnum::CUSTOMER_NOTE->value);
        data_set($modelData, 'new_values', [
            'note' => $modelData['note'],
            'details' => json_decode($modelData['details'])
        ]);
        data_set($modelData, 'group_id', $fulfilmentCustomer->group_id);
        data_set($modelData, 'organisation_id', $fulfilmentCustomer->organisation_id);
        data_set($modelData, 'shop_id', $fulfilmentCustomer->shop_id);
        data_set($modelData, 'auditable_id', $fulfilmentCustomer->customer_id);
        data_set($modelData, 'auditable_type', class_basename($fulfilmentCustomer->customer));
        data_set($modelData, 'customer_id', $fulfilmentCustomer->customer_id);
        data_forget($modelData, 'note');
        data_forget($modelData, 'details');

        return History::create($modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'note'                => ['required', 'string'],
            'details'                => ['sometimes', 'string'],
        ];
    }

    public function htmlResponse(History $history): Response
    {
        return Inertia::location(route('grp.org.fulfilments.show.crm.customers.show', [
            'organisation'       => $this->fulfilmentCustomer->organisation->slug,
            'fulfilment'         => $this->fulfilmentCustomer->fulfilment->slug,
            'fulfilmentCustomer' => $this->fulfilmentCustomer->slug,
            'tab'                => FulfilmentCustomerTabsEnum::NOTE->value,
        ]));
    }

    /**
     * @throws \Throwable
     */
    public function asController(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): History
    {
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    /**
     * @throws \Throwable
     */
    public function action(FulfilmentCustomer $fulfilmentCustomer, array $modelData): History
    {
        $this->asAction = true;
        $this->fulfilmentCustomer = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    // public string $commandSignature = 'add:fulfilment-customer-note';

    // public function asCommand($command)
    // {
    //     $c = FulfilmentCustomer::first();
    //     $this->action($c, [
    //         'note' => 'xxxxxx'
    //     ]);
    // }

}
