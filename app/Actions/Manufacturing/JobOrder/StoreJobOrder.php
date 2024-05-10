<?php

 namespace App\Actions\Manufacturing\JobOrder;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydratePalletDeliveries;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePalletDeliveries;
use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydrateUniversalSearch;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\Market\HasRentalAgreement;
use App\Actions\OrgAction;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Http\Resources\Manufacturing\JobOrderResource;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Manufacturing\JobOrder;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class StoreJobOrder extends OrgAction
{
    use AsAction;
    use WithAttributes;

    private Production $production;

    public function handle(Production $production, array $modelData): JobOrder
    {
        data_set($modelData, 'group_id', $production->group_id);
        data_set($modelData, 'organisation_id', $production->organisation_id);
        data_set($modelData, 'in_process_at', now());

        if (!Arr::get($modelData, 'reference')) {
            data_set(
                $modelData,
                'reference',
                Str::random(10) //Dummy
            );
        }

        $jobOrder = $production->jobOrders()->create($modelData);

        return $jobOrder;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return $request->user()->hasPermissionTo("productions-view.{$this->organisation->id}");
    }


    public function rules(): array
    {
        $rules = [];

        if(!request()->user() instanceof WebUser) {
            $rules = [
                'public_notes'  => ['sometimes','nullable','string','max:4000'],
                'internal_notes'=> ['sometimes','nullable','string','max:4000'],
            ];
        }

        return [
            'customer_notes'=> ['sometimes','nullable','string'],
            ...$rules
        ];
    }

    public function action(Production $production, array $modelData): JobOrder
    {
        $this->action = true;
        $this->initialisation($production->organisation, $modelData);

        return $this->handle($production, $this->validatedData);
    }

    public function asController(Organisation $organisation, Production $production, ActionRequest $request): JobOrder
    {
        $this->initialisation($organisation, $request);

        return $this->handle($production, $this->validatedData);
    }

    public function jsonResponse(JobOrder $jobOrder): JobOrderResource
    {
        return JobOrderResource::make($jobOrder) ; 
        
    }

    public function htmlResponse(JobOrder $jobOrder, ActionRequest $request): Response
    {
        $routeName = $request->route()->getName();

        return match ($routeName) {
            'grp.models.production.job-order.store' => Inertia::location(route('grp.org.manufacturing.productions.show.job-order.show', [
                'organisation'           => $jobOrder->organisation->slug,
            ])),
            default => Inertia::location(route('retina.storage.pallet-deliveries.show', [
                'jobOrder'         => $jobOrder->slug
            ]))
        };
    }

    public string $commandSignature = 'job_orders:create {production}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;


        // dummy
        $data = [
            'public_notes' => 'This is a public note for the job order.',
            'internal_notes' => 'These are internal notes for the job order.',
            'customer_notes' => 'These are internal notes for the job order.'
        ];

        try {
            $production = Production::where('slug', $command->argument('production'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());
            return 1;
        }

        $jobOrder = $this->handle($production, modelData: $data);

        $command->info("Job Order $production->reference created successfully 🎉");

        return 0;
    }


}
