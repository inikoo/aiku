<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 10 Apr 2024 16:09:45 Central Indonesia Time, Sanur , Indonesia
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">

defineProps<{
    data: object,
    tab?: string
}>()
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {SelectPaymentServiceProvider} from "@/types/select-payment-service-provider"


function paymentServiceProviderRoute(paymentServiceAccount: SelectPaymentServiceProvider) {
  console.log(route().current())
  switch (route().current()) {
    case 'grp.org.accounting.org-payment-service-providers.index':
      return route(
        'grp.org.accounting.org-payment-service-providers.show',
        [route().params['organisation'], paymentServiceAccount.org_slug]);

    default:
      return null;

  }

}
function paymentAccountRoute(paymentServiceAccount: SelectPaymentServiceProvider) {
    switch (route().current()) {
        case 'grp.org.accounting.org-payment-service-providers.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show.payment-accounts.index',
                [
                    route().params['organisation'],
                    paymentServiceAccount.org_slug
                ]
            );

    }

}

function paymentsRoute(paymentServiceAccount: SelectPaymentServiceProvider) {
    switch (route().current()) {

        case 'grp.org.accounting.org-payment-service-providers.show.payment-accounts.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show.payments.index',
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
        case 'grp.org.accounting.org-payment-service-providers.index':
            return route(
                'grp.org.accounting.org-payment-service-providers.show.payments.index',
                [
                    route().params['organisation'],
                    paymentServiceAccount.org_slug
                ]
            );

    }

}
</script>


<template>
    <Table :resource="data" class="mt-5">

        <template #cell(code)="{ item: paymentServiceProvider }">
          <Link v-if="paymentServiceProvider['org_slug']"  :href="paymentServiceProviderRoute(paymentServiceProvider)" class="specialUnderline">
                {{ paymentServiceProvider['org_code'] }}
            </Link>
          <span v-else>{{ paymentServiceProvider['code'] }}</span>
        </template>

        <template #cell(number_payment_accounts)="{ item: paymentServiceProvider }">
            <Link v-if="paymentServiceProvider['org_slug']" :href="paymentAccountRoute(paymentServiceProvider)" class="specialUnderlineSecondary">
                {{ paymentServiceProvider['number_payment_accounts'] }}
            </Link>
        </template>
        <template #cell(number_payments)="{ item: paymentServiceProvider }">
            <Link v-if="paymentServiceProvider['org_slug']" :href="paymentsRoute(paymentServiceProvider)" class="specialUnderlineSecondary">
                {{ paymentServiceProvider['number_payments'] }}
            </Link>
        </template>


    </Table>
</template>
