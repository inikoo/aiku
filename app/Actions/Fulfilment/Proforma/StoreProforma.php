<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Proforma;

use App\Actions\OrgAction;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\FulfilmentProforma;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreProforma extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public Customer $customer;
    /**
     * @var true
     */
    private bool $action = false;

    public function handle(FulfilmentCustomer $fulfilmentCustomer, array $modelData): FulfilmentProforma
    {
        data_set($modelData, 'group_id', $fulfilmentCustomer->group_id);
        data_set($modelData, 'organisation_id', $fulfilmentCustomer->organisation_id);
        data_set($modelData, 'fulfilment_id', $fulfilmentCustomer->fulfilment_id);

        /** @var FulfilmentProforma $proforma */
        $proforma = $fulfilmentCustomer->proformas()->create($modelData);

        return $proforma;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->action) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            // TODO: Raul please do the permission for the web user
            return true;
        }

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }

    public function rules(): array
    {
        return [
            'number'=> ['required', 'integer']
        ];
    }

    public function fromRetina(ActionRequest $request): FulfilmentProforma
    {
        /** @var FulfilmentCustomer $fulfilmentCustomer */
        $fulfilmentCustomer = $request->user()->customer->fulfilmentCustomer;
        $this->fulfilment   = $fulfilmentCustomer->fulfilment;

        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }


    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): FulfilmentProforma
    {
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);
        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function action(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, $modelData): FulfilmentProforma
    {
        $this->action = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $modelData);
        $this->setRawAttributes($modelData);

        return $this->handle($fulfilmentCustomer, $this->validatedData);
    }

    public string $commandSignature = 'proforma:create {fulfilment-customer}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        try {
            $fulfilmentCustomer = FulfilmentCustomer::where('slug', $command->argument('fulfilment-customer'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());
            return 1;
        }

        try {
            $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, []);
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $proforma = $this->handle($fulfilmentCustomer, modelData: $this->validatedData);

        $command->info("Proforma $proforma->slug created successfully 🎉");

        return 0;
    }


}
