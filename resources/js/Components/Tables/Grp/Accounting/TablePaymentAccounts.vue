<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

const props = defineProps<{
    data: object,
    tab?: string
}>()
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {PaymentAccount} from "@/types/payment-account";


function paymentAccountRoute(paymentAccount: PaymentAccount) {
    switch (route().current()) {

        case 'grp.org.accounting.org-payment-service-providers.show':
        case 'grp.org.accounting.org-payment-service-providers.show.payment-accounts.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show',
                [route().params['organisation'],route().params['paymentServiceProvider'], paymentAccount.slug]);
        case 'grp.org.accounting.payment-accounts.index':
            return route(
                'grp.org.accounting.payment-accounts.show',
                [route().params['organisation'],paymentAccount.slug]);

    }

}

function providersRoute(paymentAccount: PaymentAccount) {
    console.log(route().current())
    switch (route().current()) {


        case 'grp.org.accounting.payment-accounts.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show',
                [route().params['organisation'],paymentAccount.slug]);

    }
}

function paymentsRoute(paymentAccount: PaymentAccount) {
    switch (route().current()) {

        case 'grp.org.accounting.org-payment-service-providers.show.payment-accounts.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show.payments.index',
                [route().params['organisation'],route().params['paymentServiceProvider'], paymentAccount.payment_service_provider_slug]);
        case 'grp.org.accounting.payment-accounts.index':
            return route(
                'grp.org.accounting.payment-accounts.show.payments.index',
                [route().params['organisation'],paymentAccount.slug]);

    }
}


</script>


<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: paymentAccount }">
            <Link :href="paymentAccountRoute(paymentAccount)" class="specialUnderline">
                {{ paymentAccount['code'] }}
            </Link>
        </template>

        <template #cell(payment_service_provider_code)="{ item: paymentAccount }">
            <Link :href="providersRoute(paymentAccount)" class="specialUnderlineSecondary">
                {{ paymentAccount['payment_service_provider_code'] }}
            </Link>
        </template>

        <template #cell(number_payments)="{ item: paymentAccount }">
            <Link :href="paymentsRoute(paymentAccount)" class="specialUnderlineSecondary">
                {{ paymentAccount['number_payments'] }}
            </Link>
        </template>
    </Table>
</template>
