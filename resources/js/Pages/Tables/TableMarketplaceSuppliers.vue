<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import { MarketplaceSupplier } from "@/types/marketplace-supplier";

const props = defineProps<{
    data: object
}>()


function marketplaceSupplierRoute(marketplaceSupplier: MarketplaceSupplier) {
    switch (route().current()) {
        case 'procurement.marketplace-suppliers.index':
            return route(
                'procurement.marketplace-suppliers.show',
                [marketplaceSupplier.slug]);
        default:
            return route(
                'procurement.marketplace-suppliers.show',
                [marketplaceSupplier.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'mps'" class="mt-5">
        <template #cell(code)="{ item: marketplaceSupplier }">
            <Link :href="marketplaceSupplierRoute(marketplaceSupplier)">
                {{ marketplaceSupplier['code'] }}
            </Link>
        </template>
    </Table>
</template>


