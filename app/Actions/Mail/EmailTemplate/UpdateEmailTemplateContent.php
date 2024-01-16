<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailTemplate;

use App\Models\Mail\EmailTemplate;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateEmailTemplateContent
{
    use AsAction;
    use WithAttributes;

    public function handle(EmailTemplate $emailTemplate, array $content): EmailTemplate
    {
        $emailTemplate->update(
            [
                'layout' => [
                    'src'  => $content['data'],
                    'html' => $content['pagesHtml'],
                ]
            ]
        );


        return $emailTemplate;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("crm.prospects.edit");
    }

    public function rules(): array
    {
        return [
            'data'      => ['required', 'array'],
            'pagesHtml' => ['required', 'array'],

        ];
    }

    public function asController(EmailTemplate $emailTemplate, ActionRequest $request): string
    {
        $request->validate();

        $this->handle($emailTemplate, $request->validated());

        return '👍';
    }
}
