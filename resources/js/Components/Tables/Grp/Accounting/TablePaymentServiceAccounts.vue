

<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 18 Mar 2024 11:36:06 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">

const props = defineProps<{
    data: object,
    tab?: string
}>()
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {PaymentServiceAccount} from "@/types/payment-service-account"


function paymentServiceProviderRoute(paymentServiceAccount: PaymentServiceAccount) {
  console.log(route().current())
  switch (route().current()) {
    case 'grp.org.accounting.payment-service-providers.index':
      return route(
        'grp.org.accounting.payment-service-providers.show',
        [route().params['organisation'], paymentServiceAccount.slug]);

    default:
      return null;

  }

}
function paymentAccountRoute(paymentServiceAccount: PaymentServiceAccount) {
    switch (route().current()) {
        case 'grp.org.accounting.payment-service-providers.index':
            return route(
                'grp.org.accounting.payment-service-providers.show.payment-accounts.index',
                [
                    route().params['organisation'],
                    paymentServiceAccount.slug
                ]
            );

    }

}

function paymentsRoute(paymentServiceAccount: PaymentServiceAccount) {
    switch (route().current()) {

        case 'grp.org.accounting.payment-service-providers.show.payment-accounts.index':
            return route(
                'grp.org.accounting.payment-service-providers.show.payment-accounts.show.payments.index',
                [
                    route().params['organisation'],
                    route().params['paymentServiceProvider'],
                    route().params['paymentAccount']]
            );
        case 'grp.org.accounting.payment-accounts.index':
            return route(
                'grp.org.accounting.payment-accounts.show.payments.index',
                [
                    route().params['organisation'],
                    route().params['paymentAccount']
                ]
            );
        case 'grp.org.accounting.payment-service-providers.index':
            return route(
                'grp.org.accounting.payment-service-providers.show.payments.index',
                [
                    route().params['organisation'],
                    paymentServiceAccount.slug
                ]
            );

    }

}
</script>


<template>
    <Table :resource="data" class="mt-5">
        <template #cell(code)="{ item: paymentServiceProvider }">
          <Link :href="paymentServiceProviderRoute(paymentServiceProvider)" class="specialUnderline">
                {{ paymentServiceProvider['slug'] }}
            </Link>
        </template>

        <template #cell(number_payment_accounts)="{ item: paymentServiceProvider }">
            <Link :href="paymentAccountRoute(paymentServiceProvider)" class="specialUnderlineSecondary">
                {{ paymentServiceProvider['number_payment_accounts'] }}
            </Link>
        </template>
        <template #cell(number_payments)="{ item: paymentServiceProvider }">
            <Link :href="paymentsRoute(paymentServiceProvider)" class="specialUnderlineSecondary">
                {{ paymentServiceProvider['number_payments'] }}
            </Link>
        </template>


    </Table>
</template>
