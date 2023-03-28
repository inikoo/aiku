<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 19:24:57 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Payment} from "@/types/payment";

const props = defineProps<{
    data: object
}>()


function paymentsRoute(payment: Payment) {
    console.log(route().current());
    switch (route().current()) {

        case 'shops.show.orders.show':
            return route(
                'shops.show.orders.show.payments.show',
                [route().params['shop'],route().params['order'],payment.slug]);
        case 'orders.show':
            return route(
                'orders.show.payments.show',
                [route().params['order'],payment.slug]);
        case 'accounting.payment-service-providers.show.payment-accounts.show.payments.index':
            return route(
                'accounting.payment-service-providers.show.payment-accounts.show.payments.show',
                [payment.payment_service_providers_slug,payment.payment_accounts_slug, payment.slug]);
        case 'accounting.payment-service-providers.show.payments.index':
            return route(
                'accounting.payment-service-providers.show.payments.show',
                [payment.payment_service_providers_slug, payment.slug]);
        case 'accounting.payment-accounts.show.payments.index':
            return route(
                'accounting.payment-accounts.show.payments.show',
                [payment.payment_accounts_slug, payment.slug]);
        default:
            return route(
                'accounting.payments.index',
                [payment.slug]);
    }

}
</script>

<template>
    <Table :resource="data" :name="'p'" class="mt-5">
        <template #cell(reference)="{ item: payment }">
            <Link :href="paymentsRoute(payment)">
                {{ payment['reference'] }}
            </Link>
        </template>
    </Table>
</template>


