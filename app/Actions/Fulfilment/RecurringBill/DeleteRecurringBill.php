<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Jan 2025 23:52:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydrateRecurringBills;
use App\Actions\Fulfilment\RecurringBillTransaction\DeleteRecurringBillTransaction;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateRecurringBills;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateRecurringBills;
use App\Models\Fulfilment\RecurringBill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Throwable;

class DeleteRecurringBill extends OrgAction
{
    use AsController;
    use WithAttributes;

    /**
     * @throws \Throwable
     */
    public function handle(RecurringBill $recurringBill): RecurringBill
    {
        DB::transaction(function () use ($recurringBill) {

            $recurringBill->stats()->delete();


            DB::table('model_has_recurring_bills')->where('recurring_bill_id', $recurringBill->id)->delete();
            DB::table('audits')->where('auditable_type', 'RecurringBill')->where('auditable_id', $recurringBill->id)->delete();


            DB::table('invoices')->where('recurring_bill_id', $recurringBill->id)->update(['recurring_bill_id' => null]);
            DB::table('fulfilment_customers')->where('current_recurring_bill_id', $recurringBill->id)->update(['current_recurring_bill_id' => null]);
            DB::table('pallets')->where('current_recurring_bill_id', $recurringBill->id)->update(['current_recurring_bill_id' => null]);

            foreach ($recurringBill->transactions as $recurringBillTransaction) {
                DeleteRecurringBillTransaction::make()->action(
                    recurringBillTransaction: $recurringBillTransaction,
                    precursor: 'recurring_bill',
                    hydratorDelay: $this->hydratorsDelay
                );
            }



            $recurringBill->forceDelete();

        });

        FulfilmentCustomerHydrateRecurringBills::dispatch($this->fulfilment)->delay($this->hydratorsDelay);
        OrganisationHydrateRecurringBills::dispatch($this->organisation)->delay($this->hydratorsDelay);
        GroupHydrateRecurringBills::dispatch($this->organisation->group)->delay($this->hydratorsDelay);


        return $recurringBill;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("goods.{$this->group->id}.edit");
    }

    /**
     * @throws \Throwable
     */
    public function asController(RecurringBill $recurringBill, ActionRequest $request): RecurringBill
    {
        $this->initialisationFromFulfilment($recurringBill->fulfilment, $request);

        return $this->handle($recurringBill);
    }

    /**
     * @throws \Throwable
     */
    public function action(RecurringBill $recurringBill): RecurringBill
    {
        $this->asAction = true;
        $this->initialisationFromGroup($recurringBill->group, []);

        return $this->handle($recurringBill);
    }

    public function getCommandSignature(): string
    {
        return 'delete:recurring_bill {id? : Id of the recurring bill} {--all : Delete all recurring bills}';
    }

    public function asCommand(Command $command): int
    {
        $this->asAction = true;
        if ($command->option('all')) {
            $this->hydratorsDelay = 60;
            /** @var RecurringBill $recurringBill */
            foreach (RecurringBill::withTrashed()->get() as $recurringBill) {

                $this->initialisationFromFulfilment($recurringBill->fulfilment, []);
                try {
                    $recurringBillReference = $recurringBill->reference;
                    $this->handle($recurringBill);
                    $command->info("Recurring bill $recurringBillReference deleted");
                } catch (Throwable $exception) {
                    $command->error("Error deleting recurring bill ".$exception->getMessage());
                    return 1;
                }

            }



            return 0;
        }

        $recurringBill = RecurringBill::withTrashed()->where('id', $command->argument('id'))->first();

        if (!$recurringBill) {
            $command->error("Recurring Bill not found");
            return 1;
        }
        $recurringBillReference = $recurringBill->reference;

        $this->initialisationFromFulfilment($recurringBill->fulfilment, []);
        try {
            $this->handle($recurringBill);
        } catch (Throwable $exception) {
            $command->error("Error deleting recurring bill ".$exception->getMessage());

            return 1;
        }

        $command->info("Recurring bill $recurringBillReference deleted");

        return 0;
    }


}
