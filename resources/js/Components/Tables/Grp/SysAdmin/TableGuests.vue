<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import { Guest } from "@/types/guest";

const props = defineProps<{
    data: object,
    tab?:string,
}>()


function guestRoute(guest: Guest) {
    switch (route().current()) {
        case 'grp.sysadmin.guests.index':
            return route(
                'grp.sysadmin.guests.show',
                [guest.slug]);

    }
}

</script>

<template>
    <Table :resource="data" :name="tab"  class="mt-5">
        <template #cell(code)="{ item: guest }">
            <Link :href="guestRoute(guest)" class="primaryLink">
                {{ guest['code'] }}
            </Link>
        </template>
    </Table>
</template>


