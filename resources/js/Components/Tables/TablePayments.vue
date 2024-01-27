<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 19:24:57 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Pallet} from "@/types/pallet";

const props = defineProps<{
    data: object,
    tab?: string
}>()


function palletsRoute(pallet: Pallet) {
    switch (route().current()) {

        default:
            return route(
                'grp.accounting.pallets.index',
                [pallet.slug]);
    }

}
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(reference)="{ item: pallet }">
            <Link :href="palletsRoute(pallet)">
                {{ pallet['reference'] }}
            </Link>
        </template>
    </Table>
</template>


