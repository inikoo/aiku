<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

const props = defineProps<{
    data: object
}>()
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {PaymentAccount} from "@/types/payment-account";


function paymentAccountRoute(paymentAccount: PaymentAccount) {
    console.log(route().current())
    switch (route().current()) {
        case 'shops.show.accounting.payment-accounts.index':
            return route(
                'shops.show.accounting.payment-accounts.show',
                [paymentAccount.shop_slug, paymentAccount.slug]);
        case 'accounting.payment-service-providers.show':
        case 'accounting.payment-service-providers.show.payment-accounts.index':
            return route(
                'accounting.payment-service-providers.show.payment-accounts.show',
                [paymentAccount.payment_service_providers_slug, paymentAccount.slug]);
        case 'accounting.payment-accounts.index':
        default:
            return route(
                'accounting.payment-accounts.show',
                [paymentAccount.slug]);

    }

}

function paymentsRoute(paymentAccount: PaymentAccount) {
    switch (route().current()) {

        case 'accounting.payment-service-providers.show.payment-accounts.index':
            return route(
                'accounting.payment-service-providers.show.payment-accounts.show.payments.index',
                [paymentAccount.payment_service_providers_slug, paymentAccount.slug]);
        case 'accounting.payment-accounts.index':
            return route(
                'accounting.payment-accounts.show.payments.index',
                [paymentAccount.slug]);
        default:
            return route('accounting.payments.index');
    }

}
</script>


<template>
    <Table :resource="data" :name="'pa'" class="mt-5">
        <template #cell(code)="{ item: paymentAccount }">
            <Link :href="paymentAccountRoute(paymentAccount)">
                {{ paymentAccount['code'] }}
            </Link>
        </template>

        <template #cell(number_locations)="{ item: paymentAccount }">
            <Link :href="paymentsRoute(paymentAccount)">
                {{ paymentAccount['number_payments'] }}
            </Link>
        </template>
    </Table>
</template>
