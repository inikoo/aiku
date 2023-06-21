<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Prospect} from "@/types/prospect";

const props = defineProps<{
    data: object,
    tab?: string
}>()


function prospectRoute(prospect: Prospect) {
    switch (route().current()) {
        case 'shops.show.prospects.index':
            return route(
                'shops.show.prospects.show',
                [prospect.slug, prospect.slug]);
        default:
            return route(
                'prospects.show',
                [prospect.slug]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(name)="{ item: prospect }">
            <Link :href="prospectRoute(prospect)">
                {{ prospect['name'] }}
            </Link>
        </template>
    </Table>
</template>


