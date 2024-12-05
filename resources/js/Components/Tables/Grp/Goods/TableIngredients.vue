<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 24 Mar 2024 21:16:55 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';

const props = defineProps<{
    data: object,
    tab?: string
}>()

function ingredientsRoute(ingredients: {}) {
    switch (route().current()) {

        case 'grp.goods.ingredients.index':
            return route(
                'grp.goods.ingredients.show',
                [ingredients.slug]);
    }

}



</script>



<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(name)="{ item: ingredients }">
            <Link :href="ingredientsRoute(ingredients)" class="primaryLink">
                {{ ingredients['name'] }}
            </Link>
        </template>
    </Table>
</template>
