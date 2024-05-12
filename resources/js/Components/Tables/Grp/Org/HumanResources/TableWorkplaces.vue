<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 Mar 2023 16:45:18 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

const props = defineProps<{
    data: object,
    tab?: string
}>()
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Workplace} from "@/types/workplace";

function workplaceRoute(workplace: Workplace) {
    switch (route().current()) {
        case 'grp.org.hr.workplaces.index':
            return route(
                'grp.org.hr.workplaces.show',
                [route().params['organisation'],workplace.slug]);

    }

}

</script>


<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(slug)="{ item: workplace }">
            <Link :href="workplaceRoute(workplace)" class="specialUnderline">
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
