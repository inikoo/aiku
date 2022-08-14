<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 14 Aug 2022 22:42:26 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLatestDeploymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->tokenCan('root') || $this->user()->tokenCan('deployments:update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'state' => [
                'required',
                Rule::in(['error', 'deployed']),
            ],

        ];
    }
}
