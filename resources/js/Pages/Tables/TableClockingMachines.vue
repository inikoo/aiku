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
        case 'hr.working-places.show':
        case 'hr.working-places.show.clocking-machines.index':
            return route(
                "hr.working-places.show.clocking-machines.show",
                [
                    clockingMachine.workplace_slug,
                    clockingMachine.slug
                ]);
        case 'hr.clocking-machines.index':
        default:
            return route(
                'hr.clocking-machines.show',
                [clockingMachine.slug]);
    }
}
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5" >
        <template #cell(code)="{ item: clockingMachine }">
            <Link :href="clockingMachineRoute(clockingMachine)">
                {{ clockingMachine['code'] }}
            </Link>
        </template>
    </Table>
</template>
