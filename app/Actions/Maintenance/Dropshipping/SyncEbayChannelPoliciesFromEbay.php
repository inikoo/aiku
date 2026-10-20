<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 12:00:00 British Summer Time, Sheffield, UK
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance\Dropshipping;

use App\Actions\Traits\WithActionUpdate;
use App\Enums\Dropshipping\CustomerSalesChannelStatusEnum;
use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Models\Dropshipping\CustomerSalesChannel;
use App\Models\Dropshipping\EbayUser;
use App\Models\Dropshipping\Platform;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Brings the returns and postage shown on an eBay channel's settings page in line with the policies eBay
 * actually holds. It only reads from eBay: the seller's policies are never changed, because AW cannot tell
 * whether the seller still wants what was saved in AW or has since changed the policy in Seller Hub.
 */
class SyncEbayChannelPoliciesFromEbay
{
    use AsAction;
    use WithActionUpdate;

    /**
     * @return array{returns: array<string, array{aw: mixed, ebay: mixed}>, shipping: array<string, array{aw: mixed, ebay: mixed}>}|string
     */
    public function handle(EbayUser $ebayUser, bool $apply): array|string
    {
        $customerSalesChannel = $ebayUser->customerSalesChannel;

        $returnPolicy      = $this->policyOnEbay($ebayUser->getReturnPolicies(), 'returnPolicies', 'returnPolicyId', $ebayUser->return_policy_id);
        $fulfillmentPolicy = $this->policyOnEbay($ebayUser->getFulfilmentPolicies(), 'fulfillmentPolicies', 'fulfillmentPolicyId', $ebayUser->fulfillment_policy_id);

        if (is_string($returnPolicy) || is_string($fulfillmentPolicy)) {
            return is_string($returnPolicy) ? $returnPolicy : $fulfillmentPolicy;
        }

        $returnsOnEbay  = $this->returnsOnEbay($returnPolicy);
        $shippingOnEbay = $this->shippingOnEbay($ebayUser, $fulfillmentPolicy);

        $differences = [
            'returns'  => $this->differences(Arr::get($customerSalesChannel->settings, 'return', []), $returnsOnEbay),
            'shipping' => $this->differences(Arr::get($customerSalesChannel->settings, 'shipping', []), $shippingOnEbay),
        ];

        if ($apply && ($differences['returns'] || $differences['shipping'])) {
            $settings = array_filter(['return' => $differences['returns'] ? $returnsOnEbay : null, 'shipping' => $differences['shipping'] ? $shippingOnEbay : null]);

            $this->update($customerSalesChannel, ['settings' => $settings], 'settings');
            $this->update($ebayUser, [
                'settings' => $settings,
                'data'     => ['return_policy' => $returnPolicy, 'fulfillment_policy' => $fulfillmentPolicy],
            ], ['settings', 'data']);
        }

        return $differences;
    }

    /**
     * @return array<string, mixed>|string
     */
    private function policyOnEbay(mixed $response, string $listKey, string $idKey, ?string $policyId): array|string
    {
        if ($error = EbayUser::ebayResponseError($response)) {
            return 'eBay did not answer: '.$error;
        }

        $policy = collect(Arr::get($response, $listKey, []))->firstWhere($idKey, $policyId);

        return $policy ?? "eBay no longer has the channel's policy $policyId";
    }

    /**
     * @param  array<string, mixed>  $returnPolicy
     * @return array<string, mixed>
     */
    private function returnsOnEbay(array $returnPolicy): array
    {
        $accepted = (bool) Arr::get($returnPolicy, 'returnsAccepted');

        return array_filter([
            'accepted'    => $accepted,
            'payer'       => $accepted ? Arr::get($returnPolicy, 'returnShippingCostPayer') : null,
            'within'      => $accepted ? Arr::get($returnPolicy, 'returnPeriod.value') : null,
            'description' => Arr::get($returnPolicy, 'description'),
        ], fn ($value) => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $fulfillmentPolicy
     * @return array<string, mixed>
     */
    private function shippingOnEbay(EbayUser $ebayUser, array $fulfillmentPolicy): array
    {
        $service     = Arr::get($fulfillmentPolicy, 'shippingOptions.0.shippingServices.0', []);
        $serviceCode = Arr::get($service, 'shippingServiceCode');
        $carrierCode = Arr::get($service, 'shippingCarrierCode');

        return array_filter([
            ...Arr::get($ebayUser->getServicesWithCarrierInfo(), (string) $serviceCode, [
                'service_code' => $serviceCode,
                'service_name' => $serviceCode,
                'carrier_code' => $carrierCode,
                'carrier_name' => $carrierCode,
            ]),
            'price'             => Arr::get($service, 'shippingCost.value'),
            'max_dispatch_time' => (string) Arr::get($fulfillmentPolicy, 'handlingTime.value'),
        ], fn ($value) => filled($value));
    }

    /**
     * @param  array<string, mixed>  $inAw
     * @param  array<string, mixed>  $onEbay
     * @return array<string, array{aw: mixed, ebay: mixed}>
     */
    private function differences(array $inAw, array $onEbay): array
    {
        $differences = [];

        foreach ($onEbay as $key => $ebayValue) {
            $awValue = Arr::get($inAw, $key);

            $same = is_numeric($awValue) && is_numeric($ebayValue)
                ? (float) $awValue === (float) $ebayValue
                : $awValue === $ebayValue;

            if (!$same) {
                $differences[$key] = ['aw' => $awValue, 'ebay' => $ebayValue];
            }
        }

        return $differences;
    }

    public function getCommandSignature(): string
    {
        return 'repair:ebay_policies_from_ebay {customerSalesChannel?} {--apply : Save the differences, without it they are only listed}';
    }

    public function asCommand(Command $command): void
    {
        Nightwatch::dontSample();

        $apply = (bool) $command->option('apply');

        $customerSalesChannels = CustomerSalesChannel::query()
            ->where('platform_id', Platform::where('type', PlatformTypeEnum::EBAY)->value('id'))
            ->where('status', CustomerSalesChannelStatusEnum::OPEN)
            ->when($command->argument('customerSalesChannel'), fn ($query, $slug) => $query->where('slug', $slug))
            ->with('user')
            ->get();

        $rows = [];

        foreach ($customerSalesChannels as $customerSalesChannel) {
            $ebayUser = $customerSalesChannel->user;

            if (!$ebayUser instanceof EbayUser || !$ebayUser->return_policy_id || !$ebayUser->fulfillment_policy_id) {
                continue;
            }

            $result = $this->handle($ebayUser, $apply);

            if (is_string($result)) {
                $rows[] = [$customerSalesChannel->slug, 'skipped', $result];
                continue;
            }

            foreach ($result as $section => $differences) {
                foreach ($differences as $key => $difference) {
                    $rows[] = [$customerSalesChannel->slug, "$section.$key", json_encode($difference['aw']).' -> '.json_encode($difference['ebay'])];
                }
            }
        }

        $command->table(['Channel', 'Setting', 'AW -> eBay'], $rows);
        $command->info($apply ? 'Saved what eBay holds onto the channels above.' : 'Nothing saved. Run again with --apply to save what eBay holds.');
    }
}
