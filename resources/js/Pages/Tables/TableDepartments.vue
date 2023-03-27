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
    data: object
}>()


function departmentRoute(department: Department) {
    switch (route().current()) {
        case 'shops.show.departments.index':
            return route(
                'shops.show.departments.show',
                [department.shop_slug, department.slug]);
        default:
            return route(
                'departments.show',
                [department.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="'dep'" class="mt-5">
        <template #cell(code)="{ item: department }">
            <Link :href="departmentRoute(department)">
                {{ department['code'] }}
            </Link>
        </template>

    </Table>
</template>


