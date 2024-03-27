<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Invoice} from "@/types/invoice";

const props = defineProps<{
    data: object,
    tab?: string
}>()


function invoiceRoute(invoice: Invoice) {
  console.log(route().current())
    switch (route().current()) {
        case 'shops.show.invoices.index':
            return route(
                'shops.show.invoices.show',
                [invoice.slug, invoice.slug]);
      case 'grp.org.fulfilments.show.operations.invoices.index':
            return route(
                'grp.org.fulfilments.show.operations.invoices.show',
                [route().params['organisation'],route().params['fulfilment'],invoice.slug]);
        default:
            return route(
                'grp.org.accounting.invoices.show',
                [route().params['organisation'],invoice.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(number)="{ item: invoice }">
            <Link :href="invoiceRoute(invoice)">
                {{ invoice["number"]}}
            </Link>
        </template>

      <template #cell(net)="{ item: invoice }">

          {{ invoice["net"]}}  {{ invoice["currency_code"]}}  {{ invoice["currency_symbol"]}}

      </template>

    </Table>
</template>


