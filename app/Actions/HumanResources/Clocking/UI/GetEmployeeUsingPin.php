<?php


namespace App\Actions\HumanResources\Clocking\UI;

use App\Http\Resources\HumanResources\EmployeeResource;
use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetEmployeeUsingPin 
{
    use AsAction;

    public function handle(string $pin)
    {
        
        $employee = Employee::where('pin', $pin)->first();

        
        if ($employee) {
            return $employee;
        } else {
            
            throw new \Exception('Employee not found');
        }
    }

    public function asController(ActionRequest $request)
    {
        $pin = $request->input('pin');

        return $this->handle($pin);
    }

    public function jsonResponse(Employee $employee): EmployeeResource
    {
        return new EmployeeResource($employee);
    }
}