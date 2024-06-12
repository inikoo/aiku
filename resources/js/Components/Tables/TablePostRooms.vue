<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {PostRoom} from "@/types/postRoom";
defineProps<{
    data: object,
    tab?:string,
}>()


function mailRoute(postRoom: PostRoom) {
    switch (route().current()) {
        case 'mail.post_rooms.index':
            return route(
                'mail.post_rooms.show',
                [postRoom.code]);
    }
}

</script>

<template>
    <Table :resource="data" :name="tab"  class="mt-5">
        <template #cell(code)="{ item: postRoom }">
            <Link :href="mailRoute(postRoom)">
                {{ postRoom['code'] }}
            </Link>
        </template>
    </Table>


</template>


