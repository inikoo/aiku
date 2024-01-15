<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Oct 2023 16:11:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Enums\Mail\Mailshot\MailshotTypeEnum;
use App\Models\Mail\Mailshot;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateMailshotContent
{
    use AsAction;
    use WithAttributes;

    public Mailshot $mailshot;


    public function handle(Mailshot $mailshot, array $content): Mailshot
    {
        $mailshot->update(
            [
                'layout' => [
                    'src'  => $content['data'],
                    'html' => $content['pagesHtml'],
                ]
            ]
        );


        return $mailshot;
    }

    public function authorize(ActionRequest $request): bool
    {
        $mailshot = $this->mailshot;
        if ($mailshot->type == MailshotTypeEnum::PROSPECT_MAILSHOT) {
            return $request->user()->hasPermissionTo("crm.prospects.edit");
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'data'      => ['required', 'array'],
            'pagesHtml' => ['required', 'array'],

        ];
    }

    public function asController(Mailshot $mailshot, ActionRequest $request): string
    {
        $this->mailshot = $mailshot;
        $request->validate();

        $this->handle($mailshot, $request->validated());

        return '👍';
    }


}
