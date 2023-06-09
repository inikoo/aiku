<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Department} from "@/types/department";
import NewItem from "@/Components/NewItem.vue";
import { ref } from "vue";
import Button from "@/Components/Elements/Buttons/Button.vue";

const props = defineProps<{
    data: object
}>();


function departmentRoute(department: Department) {
    switch (route().current()) {
        case 'shops.show.catalogue.hub':
            return route(
                'catalogue.shop.departments.show',
                [route().params['shop'], department.slug]);
        default:
            return route(
                'catalogue.departments.show',
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


