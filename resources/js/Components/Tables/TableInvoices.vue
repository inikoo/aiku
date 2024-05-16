<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import Table from '@/Components/Table/Table.vue'
import { Invoice } from "@/types/invoice"
import { useLocaleStore } from '@/Stores/locale'
import { useFormatTime } from "@/Composables/useFormatTime copy"


const props = defineProps<{
    data: {}
    tab?: string
}>()


function invoiceRoute(invoice: Invoice) {
    // console.log(route().current())
    switch (route().current()) {
        case 'shops.show.invoices.index':
            return route(
                'shops.show.invoices.show',
                [invoice.slug, invoice.slug])
        case 'grp.org.fulfilments.show.operations.invoices.index':
            return route(
                'grp.org.fulfilments.show.operations.invoices.show',
                [route().params['organisation'], route().params['fulfilment'], invoice.slug]);
        case 'grp.org.fulfilments.show.crm.customers.show':
            return route(
                'grp.org.fulfilments.show.crm.customers.show.invoices.show',
                [route().params['organisation'], route().params['fulfilment'], route().params['fulfilmentCustomer'], invoice.slug])
        default:
            return route(
                'grp.org.accounting.invoices.show',
                [route().params['organisation'], invoice.slug])
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(number)="{ item: invoice }">
            <Link :href="invoiceRoute(invoice)" class="primaryLink py-0.5">
                {{ invoice.number }}
            </Link>
        </template>

        <template #cell(date)="{ item }">
            <div class="text-gray-500">
                {{ useFormatTime(item.date) }}
            </div>
        </template>

        <template #cell(net_amount)="{ item: invoice }">
            <div class="text-gray-500">
                {{ useLocaleStore().currencyFormat(invoice.currency_code, invoice.net_amount) }}
            </div>
        </template>

      <template #cell(total_amount)="{ item: invoice }">
        <div class="text-gray-500">
          {{ useLocaleStore().currencyFormat(invoice.currency_code, invoice.total_amount) }}
        </div>
      </template>

    </Table>
</template>
