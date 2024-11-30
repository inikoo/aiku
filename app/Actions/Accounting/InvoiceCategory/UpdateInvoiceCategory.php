<?php

/*
 * author Arya Permana - Kirin
 * created on 20-11-2024-09h-05m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Accounting\InvoiceCategory;

use App\Actions\GrpAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Accounting\Invoice\InvoiceCategoryStateEnum;
use App\Models\Accounting\InvoiceCategory;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateInvoiceCategory extends GrpAction
{
    use WithNoStrictRules;
    use WithActionUpdate;

    public function handle(InvoiceCategory $invoiceCategory, array $modelData): InvoiceCategory
    {
        $invoiceCategory = $this->update($invoiceCategory, $modelData);

        return $invoiceCategory;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return false;
    }


    public function rules(): array
    {
        $rules = [
            'name' => ['sometimes', 'string'],
            'state' => ['sometimes', Rule::enum(InvoiceCategoryStateEnum::class)]
        ];
        if (!$this->strict) {
            $rules             = $this->noStrictUpdateRules($rules);
        }
        return $rules;
    }

    public function action(InvoiceCategory $invoiceCategory, array $modelData, int $hydratorsDelay = 0, bool $strict = true): InvoiceCategory
    {
        $this->asAction = true;
        $this->strict   = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisation($invoiceCategory->group, $modelData);

        return $this->handle($invoiceCategory, $this->validatedData);
    }


}
