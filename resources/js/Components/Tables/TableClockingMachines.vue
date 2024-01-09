<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
const props = defineProps<{
    data: object,
    tab?: string
}>()

import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {ClockingMachine} from "@/types/clocking-machine";

function clockingMachineRoute(clockingMachine : ClockingMachine) {
    switch (route().current()) {
        case 'grp.org.hr.workplaces.show':
        case 'grp.org.hr.workplaces.show.clocking-machines.index':
            return route(
                "hr.workplaces.show.clocking-machines.show",
                [
                    clockingMachine.workplace_slug,
                    clockingMachine.slug
                ]);
        case 'grp.org.hr.clocking-machines.index':
        default:
            return route(
                'grp.org.hr.clocking-machines.show',
                [clockingMachine.slug]);
    }
}
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5" >
        <template #cell(name)="{ item: clockingMachine }">
            <Link :href="clockingMachineRoute(clockingMachine)">
                {{ clockingMachine['name'] }}
            </Link>
        </template>
    </Table>
</template>
