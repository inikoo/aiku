
<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Thu, 15 Sept 2022 18:41:25 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Head, Link} from '@inertiajs/vue3';
import PageHeading from '@/Components/Headings/PageHeading.vue';
import Table from '@/Components/Table/Table.vue';

defineProps(['warehouses', 'title','pageHead']);

</script>

<template layout="App">
    <Head :title="title" />
    <PageHeading :data="pageHead"></PageHeading>
    <Table :resource="warehouses" class="mt-5">
        <template #cell(code)="{ item: warehouse }">
            <Link :href="route('inventory.warehouses.show',warehouse.slug)">
                {{ warehouse.code }}
            </Link>
        </template>
        <template #cell(number_warehouse_areas)="{ item: warehouse }">
            <Link :href="route('inventory.warehouses.show.warehouse_areas.index',warehouse.slug)">
                {{ warehouse['number_warehouse_areas'] }}
            </Link>
        </template>
    </Table>
</template>

