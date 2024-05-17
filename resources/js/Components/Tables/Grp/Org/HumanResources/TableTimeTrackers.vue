<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 16 May 2024 17:12:16 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Clocking} from "@/types/clocking";

const props = defineProps<{
    data: object,
    tab?: string
}>()

function clockingRoute(clocking: Clocking) {
    switch (route().current()) {
        case 'grp.org.hr.clocking-machines.show':
            return route(
                'grp.org.hr.clocking-machines.show.clockings.show',
                [route().params['clockingMachine'], clocking.slug]);

        case 'grp.org.hr.workplaces.show.clocking-machines.show':
            return route(
                'grp.org.hr.workplaces.show.clocking-machines.show.clockings.show',
                [route().params['workplace'],route().params['clockingMachine'], clocking.slug]);
        case 'grp.org.hr.workplaces.show.clockings.index':
            return route(
                'grp.org.hr.workplaces.show.clockings.show',
                [clocking.workplace_slug, clocking.slug]);
        case 'grp.org.hr.clocking-machines.clockings.index':
            return route(
                'grp.org.hr.clocking-machines.show.clockings.show',
                [clocking.clocking_machine_slug, clocking.slug]);
        case 'grp.org.hr.workplaces.show.clocking-machines.show.clockings.index':
            return route(
                'grp.org.hr.workplaces.show.clocking-machines.show.clockings.show',
                [clocking.workplace_slug, clocking.clocking_machine_slug, clocking.slug]
            )
        default:
            return route(
                'grp.org.hr.clockings.show',
                [clocking.slug]);
    }

}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(slug)="{ item: clocking }">
            <Link :href="clockingRoute(clocking)">
                {{ clocking['slug'] }}
            </Link>
        </template>
    </Table>
</template>


