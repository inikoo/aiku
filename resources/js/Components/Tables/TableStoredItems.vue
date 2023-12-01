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

function storedItemRoute(storedItem) {
    switch (route().current()) {
        case 'grp.fulfilment.stored-items.index':
            return route(
                'grp.fulfilment.stored-items.show',
                [storedItem.slug]);
        default:
            return route(
                'grp.fulfilment.stored-items.show',
                [storedItem.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'stored_items'" class="mt-5">
        <template #cell(reference)="{ item: value }">
            <Link :href="storedItemRoute(value)">
                {{ value['reference'] }}
            </Link>
        </template>
    </Table>
</template>
