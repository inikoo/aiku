<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
// import {ref} from 'vue'
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import WorkingPlace from "@/Pages/HumanResources/WorkingPlace.vue";
import {Workplace} from "@/types/workplace";

const props = defineProps<{
    data: object
}>()


function workingPlaceRoute(workplace: Workplace) {
    switch (route().current()) {
        case 'hr.working-places.index':
            return route(
                'hr.working-places.show',
                [workplace.slug]);

    }
}
</script>

<template>
    <Table :resource="data" :name="'wrkplc'" class="mt-5"  >
        <template #cell(slug)="{ item: workplace }">
            <Link :href="workingPlaceRoute(workplace)">
                {{ workplace['slug'] }}
            </Link>
        </template>
        <template #cell(name)="{ item: workplace }">
            {{ workplace['name'] }}
        </template>
        <template #cell(type)="{ item: workplace }">
            {{ workplace['type'] }}
        </template>
    </Table>
</template>
