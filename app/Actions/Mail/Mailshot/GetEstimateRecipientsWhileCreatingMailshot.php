<?php

namespace App\Actions\Mail\Mailshot;

use App\Models\Market\Shop;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class GetEstimateRecipientsWhileCreatingMailshot
{
    use AsAction;
    use WithAttributes;
    use WithRecipientsInput;

    private bool $asAction = false;

    public function handle(Shop $parent, $recipientsData): int
    {
        return GetEstimatedNumberRecipients::run($parent, $recipientsData);
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
            'recipient_builder_type' => ['required', 'string', 'in:query,prospects,custom_prospects_query'],
            'recipient_builder_data' => ['required', 'array']
        ];
    }


    /**
     * @throws \Exception
     */
    public function asController(Shop $shop, ActionRequest $request): array
    {


        $this->fillFromRequest($request);
        $validateAttributes = $this->validateAttributes();
        $recipientsData     = $this->postProcessRecipients($validateAttributes);

        $count = $this->handle(
            $shop,
            $recipientsData
        );

        return [
            'type'  => $recipientsData['recipient_builder_type'],
            'count' => $count
        ];
    }
}
