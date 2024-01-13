<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 08:23:57 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Query;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Helpers\Query;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateQuery
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;
    use WithQueryCompiler;

    private bool $asAction = false;


    /**
     * @throws \Exception
     */
    public function handle(Query $query, array $modelData): Query
    {
        if (Arr::exists($modelData, 'constrains')) {
            data_set($modelData, 'compiled_constrains', $this->compileConstrains($modelData['constrains']));
        }
        return $this->update($query, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("crm.prospects.edit");
    }

    public function rules(): array
    {
        return [
            'name'       => ['sometimes', 'string'],
            'constrains' => ['sometimes', 'array'],
        ];
    }

    /**
     * @throws \Exception
     */
    public function action(Query $query, array $objectData): Query
    {
        $this->asAction  = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();
        return $this->handle($query, $validatedData);
    }

    /**
     * @throws \Exception
     */
    public function asController(Query $query, ActionRequest $request): Query
    {
        $request->validate();
        return $this->handle($query, $request->validated());
    }
}
