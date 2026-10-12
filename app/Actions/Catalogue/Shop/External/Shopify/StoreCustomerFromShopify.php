<?php

namespace App\Actions\Catalogue\Shop\External\Shopify;

use App\Actions\CRM\Customer\StoreCustomer;
use App\Actions\CRM\Customer\UpdateCustomer;
use App\Actions\OrgAction;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Helpers\Country;
use Illuminate\Support\Arr;

class StoreCustomerFromShopify extends OrgAction
{
    public function handle(Shop $shop, array $shopifyCustomer, ?array $fallbackAddress = null): Customer
    {
        $customerId = str_replace('gid://shopify/Customer/', '', (string) Arr::get($shopifyCustomer, 'id'));
        $email      = Arr::get($shopifyCustomer, 'email');

        $customer = $this->findCustomer($shop, $customerId, $email);

        if ($customer) {
            return $customer;
        }

        $customerAddress = Arr::get($shopifyCustomer, 'defaultAddress') ?: $fallbackAddress;

        $contactName = trim(Arr::get($shopifyCustomer, 'firstName', '').' '.Arr::get($shopifyCustomer, 'lastName', ''))
            ?: trim((string) Arr::get($customerAddress, 'name'))
                ?: $email;

        $customerData = [
            'contact_name' => $contactName,
            'company_name' => Arr::get($customerAddress, 'company') ?: $email,
            'email'        => $email,
            'phone'        => Arr::get($shopifyCustomer, 'phone') ?: Arr::get($customerAddress, 'phone'),
            'external_id'  => $customerId,
        ];

        if ($customerAddress) {
            data_set($customerData, 'contact_address', $this->getFormattedAddress($customerAddress));
        }

        return StoreCustomer::make()->action($shop, $customerData, strict: false);
    }

    /**
     * A guest checkout has no Shopify customer, so the buyer is kept by email instead.
     */
    public function handleFromOrder(Shop $shop, array $shopifyOrder): Customer
    {
        $deliveryAddress = Arr::get($shopifyOrder, 'shippingAddress') ?: Arr::get($shopifyOrder, 'billingAddress');

        if ($shopifyCustomer = Arr::get($shopifyOrder, 'customer')) {
            $customer = $this->handle($shop, $shopifyCustomer, $deliveryAddress);
        } else {
            $email = Arr::get($shopifyOrder, 'email');

            $customer = $this->handle($shop, [
                'id'        => $email ?: Arr::get($shopifyOrder, 'id'),
                'email'     => $email,
                'phone'     => Arr::get($shopifyOrder, 'phone'),
                'firstName' => Arr::get($deliveryAddress, 'firstName'),
                'lastName'  => Arr::get($deliveryAddress, 'lastName'),
            ], $deliveryAddress);
        }

        return $this->syncCustomerAddresses($customer, $shopifyOrder);
    }

    /**
     * An order is born from the customer's own addresses, so the customer is given this order's billing and
     * shipping addresses first and the order is created right, with nothing to correct on it afterwards.
     */
    private function syncCustomerAddresses(Customer $customer, array $shopifyOrder): Customer
    {
        $billingAddress  = $this->getAddressWithCountry(Arr::get($shopifyOrder, 'billingAddress'));
        $shippingAddress = $this->getAddressWithCountry(Arr::get($shopifyOrder, 'shippingAddress'));

        $contactAddress  = $billingAddress ?? $shippingAddress;
        $deliveryAddress = $shippingAddress ?? $billingAddress;

        if (!$contactAddress) {
            return $customer;
        }

        $customer = UpdateCustomer::make()->action(customer: $customer, modelData: ['contact_address' => $contactAddress], strict: false);

        if (!$customer->delivery_address_id) {
            $customer->updateQuietly(['delivery_address_id' => $customer->address_id]);
        }

        if ($deliveryAddress != $contactAddress || $customer->delivery_address_id !== $customer->address_id) {
            $customer = UpdateCustomer::make()->action(customer: $customer, modelData: ['delivery_address' => $deliveryAddress], strict: false);
        }

        return $customer->refresh();
    }

    private function getAddressWithCountry(?array $shopifyAddress): ?array
    {
        if (!$shopifyAddress) {
            return null;
        }

        $address = $this->getFormattedAddress($shopifyAddress);

        return $address['country_id'] ? $address : null;
    }

    private function findCustomer(Shop $shop, string $customerId, ?string $email): ?Customer
    {
        $customer = Customer::where('shop_id', $shop->id)
            ->where('external_id', $customerId)
            ->first();

        if ($customer || blank($email)) {
            return $customer;
        }

        return Customer::where('shop_id', $shop->id)
            ->whereRaw('lower(email) = lower(?)', [$email])
            ->first();
    }

    /**
     * Shopify uses countryCodeV2 (ISO2 format like "US", "GB")
     *
     * @param array $address
     * @return array
     */
    public function getFormattedAddress(array $address): array
    {
        $country = Country::where('code', Arr::get($address, 'countryCodeV2'))->first();

        return [
            'address_line_1'      => Arr::get($address, 'address1') ?? '',
            'address_line_2'      => Arr::get($address, 'address2'),
            'sorting_code'        => null,
            'postal_code'         => Arr::get($address, 'zip'),
            'dependent_locality'  => null,
            'locality'            => Arr::get($address, 'city'),
            'administrative_area' => Arr::get($address, 'provinceCode') ?: Arr::get($address, 'province'),
            'country_code'        => $country?->code,
            'country_id'          => $country?->id,
        ];
    }
}
