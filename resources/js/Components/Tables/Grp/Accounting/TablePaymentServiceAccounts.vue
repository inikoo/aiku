

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
function paymentAccountRoute(paymentAccount: PaymentAccount) {
    switch (route().current()) {
        case 'shops.show.accounting.payment-accounts.index':
            return route(
                'shops.show.accounting.payment-accounts.show',
                [paymentAccount.shop_slug, paymentAccount.slug]);
        case 'grp.org.accounting.payment-service-providers.show':
        case 'grp.org.accounting.payment-service-providers.show.payment-accounts.index':
            return route(
                'grp.org.accounting.payment-service-providers.show.payment-accounts.show',
                [paymentAccount.payment_service_providers_slug, paymentAccount.slug]);
        case 'grp.org.accounting.payment-accounts.index':
        default:
            return route(
                'grp.org.accounting.payment-accounts.show',
                [paymentAccount.slug]);

    }

}

function paymentsRoute(paymentAccount: PaymentAccount) {
    switch (route().current()) {

        case 'grp.org.accounting.payment-service-providers.show.payment-accounts.index':
            return route(
                'grp.org.accounting.payment-service-providers.show.payment-accounts.show.payments.index',
                [paymentAccount.payment_service_providers_slug, paymentAccount.slug]);
        case 'grp.org.accounting.payment-accounts.index':
            return route(
                'grp.org.accounting.payment-accounts.show.payments.index',
                [paymentAccount.slug]);
        default:
            return route('grp.org.accounting.payments.index');
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

    </Table>
</template>
