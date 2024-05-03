<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';

const props = defineProps<{
    data: object,
    tab?: string
}>()


function supplierDeliveryRoute(supplierDelivery: { } ) {
    switch (route().current()) {
        case 'grp.org.procurement.supplier-deliveries.index':
            return route(
                'grp.org.procurement.supplier-deliveries.show',
                [supplierDelivery.slug]);
        default:
            return route(
                'grp.org.procurement.supplier-deliveries.show',
                [supplierDelivery.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(number)="{ item: supplierDelivery }">
            <Link :href="supplierDeliveryRoute(supplierDelivery)">
                {{ supplierDelivery['number'] }}
            </Link>
        </template>
    </Table>
</template>


