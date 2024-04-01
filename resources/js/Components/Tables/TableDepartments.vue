<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Department} from "@/types/department";
import {Family} from "@/types/family";

const props = defineProps<{
    data: object,
    tab?:string
}>();


function departmentRoute(department: Department) {
    switch (route().current()) {
      case "grp.org.shops.show.catalogue.departments.index":
        return route(
          'grp.org.shops.show.catalogue.departments.show',
          [route().params['organisation'],route().params['shop'], department.slug]);
        case 'grp.org.shops.index':
            return route(
                'grp.org.shops.show.catalogue.departments.show',
                [route().params['organisation'],department.shop_slug, department.slug]);
        default:
            return null;
    }
}

function shopRoute(department: Department) {
    switch (route().current()) {
        case 'grp.org.shops.index':
            return route(
                "grp.org.shops.show.catalogue.dashboard",
                [route().params["organisation"], department.shop_slug]);
    }
}

</script>

<template>

    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(code)="{ item: department }">
            <Link :href="departmentRoute(department)" class="specialUnderline">
                {{ department['code'] }}
            </Link>
        </template>
        <template #cell(shop_code)="{ item: department }">
            <Link :href="shopRoute(department)" class="specialUnderlineSecondary">
                {{ department["shop_code"] }}
            </Link>
        </template>
    </Table>
</template>


