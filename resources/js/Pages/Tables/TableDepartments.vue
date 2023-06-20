<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Department} from "@/types/department";

const props = defineProps<{
    data: object,
    tab?:string
}>();


function departmentRoute(department: Department) {
    switch (route().current()) {
        case 'shops.show':
            return route(
                'shops.show.departments.show',
                [route().params['shop'], department.slug]);
        default:
            return route(
                'shops.departments.show',
                [department.slug]);
    }
}

</script>

<template>

    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: department }">
            <Link :href="departmentRoute(department)">
                {{ department['code'] }}
            </Link>
        </template>
    </Table>
</template>


