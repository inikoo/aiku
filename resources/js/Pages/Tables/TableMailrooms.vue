<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Mailroom} from "@/types/mailroom.ts";
const props = defineProps<{
    data: object,
    tab?:string,
}>()


function mailRoute(mailroom: Mailroom) {
    switch (route().current()) {
        case 'mail.mailrooms.index':
            return route(
                'mail.mailrooms.show',
                [mailroom.code]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab"  class="mt-5">
        <template #cell(code)="{ item: mailroom }">
            <Link :href="mailRoute(mailroom)">
                {{ mailroom['code'] }}
            </Link>
        </template>
    </Table>


</template>


