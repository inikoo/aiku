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
        case 'fulfilment.stored-items.index':
            return route(
                'fulfilment.stored-items.show',
                [storedItem.slug]);
        default:
            return route(
                'fulfilment.stored-items.show',
                [storedItem.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(slug)="{ item: value }">
            <Link :href="storedItemRoute(value)">
                {{ value['slug'] }}
            </Link>
        </template>
    </Table>
</template>
