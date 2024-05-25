<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Thu, 16 May 2024 17:12:16 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Clocking} from "@/types/clocking";
import Icon from "@/Components/Icon.vue";
import {library} from "@fortawesome/fontawesome-svg-core";
import {faPlus} from "@fas";
import {faClock, faDoorClosed, faDoorOpen} from "@fal";
import { useFormatTime } from '@/Composables/useFormatTime'

const props = defineProps<{
    data: object,
    tab?: string
}>()

library.add(faClock, faDoorOpen, faDoorClosed)

function clockingRoute(clocking: Clocking) {
    switch (route().current()) {
        case 'grp.org.hr.clocking_machines.show':
            return route(
                'grp.org.hr.clocking_machines.show.clockings.show',
                [route().params['clockingMachine'], clocking.slug]);

        case 'grp.org.hr.workplaces.show.clocking_machines.show':
            return route(
                'grp.org.hr.workplaces.show.clocking_machines.show.clockings.show',
                [route().params['workplace'],route().params['clockingMachine'], clocking.slug]);
        case 'grp.org.hr.workplaces.show.clockings.index':
            return route(
                'grp.org.hr.workplaces.show.clockings.show',
                [clocking.workplace_slug, clocking.slug]);
        case 'grp.org.hr.clocking_machines.clockings.index':
            return route(
                'grp.org.hr.clocking_machines.show.clockings.show',
                [clocking.clocking_machine_slug, clocking.slug]);
        case 'grp.org.hr.workplaces.show.clocking_machines.show.clockings.index':
            return route(
                'grp.org.hr.workplaces.show.clocking_machines.show.clockings.show',
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

        <!-- Column: Clocked In -->
        <template #cell(starts_at)="{ item: clocking }">
            <div :href="'x'">
                {{ useFormatTime(clocking.starts_at, {formatTime: 'hm'}) }}
            </div>
        </template>

        <!-- Column: Clocked Out -->
        <template #cell(ends_at)="{ item: clocking }">
            <div :href="'x'">
                {{ useFormatTime(clocking.ends_at, {formatTime: 'hm'}) }}
            </div>
        </template>
        <template #cell(status)="{ item: clocking }">
            <Icon :data="clocking['status']" class="px-1"/>
        </template>
        <template #cell(action)="{ item: clocking }">
            <Icon :data="clocking['action']" class="px-1"/>
        </template>
    </Table>
</template>
