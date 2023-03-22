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
    data: object
}>()


function invoiceRoute(invoice: Invoice) {
    switch (route().current()) {
        case 'shops.show.invoices.index':
            return route(
                'shops.show.invoices.show',
                [invoice.slug, invoice.slug]);
        default:
            return route(
                'invoices.show',
                [invoice.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'inv'" class="mt-5">
        <template #cell(number)="{ item: invoice }">
            <Link :href="route(invoiceRoute(invoice))">
                {{ invoice["name"]}}
            </Link>
        </template>

    </Table>
</template>


