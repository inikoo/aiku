<?php

namespace App\Actions\Production\JobOrder;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Production\JobOrder;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Console\Command;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Symfony\Component\HttpFoundation\Response;

class UpdateJobOrder extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    private bool $action = false;

    private JobOrder $jobOrder;

    public function handle(JobOrder $jobOrder, array $modelData): JobOrder
    {
        return $this->update($jobOrder, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return $request->user()->authTo("productions-view.{$this->organisation->id}");
    }

    public function rules(): array
    {
        $rules = [];

        if (!request()->user() instanceof WebUser) {
            $rules = [
                'public_notes'  => ['sometimes','nullable','string','max:4000'],
                'internal_notes' => ['sometimes','nullable','string','max:4000'],
            ];
        }

        return [
            'customer_notes' => ['sometimes','nullable','string','max:4000'],
            ...$rules
        ];
    }

    public function asController(Organisation $organisation, JobOrder $jobOrder, ActionRequest $request): JobOrder
    {
        $this->jobOrder = $jobOrder;
        $this->initialisation($jobOrder->organisation, $request);
        return $this->handle($jobOrder, $this->validatedData);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function action(Organisation $organisation, JobOrder $jobOrder, $modelData): JobOrder
    {
        $this->asAction = true;
        $this->jobOrder = $jobOrder;
        $this->initialisation($jobOrder->organisation, $modelData);
        $this->setRawAttributes($modelData);

        return $this->handle($jobOrder, $this->validatedData);
    }

    public function htmlResponse(JobOrder $jobOrder, ActionRequest $request): Response
    {
        $routeName = $request->route()->getName();

        return match ($routeName) {
            'grp.models.production.job-order.update' => Inertia::location(route('grp.org.productions.show.job-order.show', [
                'organisation'           => $jobOrder->organisation->slug,
            ])),
        };
    }

    public string $commandSignature = 'job-orders:update {job-order}';

    public function asCommand(Command $command): int
    {
        $this->asAction = true;

        // Dummy test Data
        // $data = [
        //     'public_notes' => 'Update.',
        //     'internal_notes' => 'Update. ',
        //     'customer_notes' => 'Update.'
        // ];

        try {
            $jobOrder = JobOrder::where('slug', $command->argument('job-order'))->firstOrFail();
        } catch (Exception $e) {
            $command->error($e->getMessage());
            return 1;
        }

        $jobOrder = $this->handle($jobOrder, modelData: $this->validatedData);

        $command->info("Job Order $jobOrder->reference updated successfully 🎉");

        return 0;
    }
}
