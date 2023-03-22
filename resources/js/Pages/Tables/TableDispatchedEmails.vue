<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {DispatchedEmail} from "@/types/dispatched-email";

const props = defineProps<{
    data: object
}>()


function dispatchedEmailRoute(dispatchedEmail: DispatchedEmail) {
    switch (route().current()) {
        case 'mail.dispatched-emails.index':
            return route(
                'mail.dispatched-emails.show',
                [dispatchedEmail.outbox_id, dispatchedEmail.id]);
        default:
            return route(
                'dispatched-emails.show',
                [dispatchedEmail.id]);
    }
}



</script>

<template>
    <Table :resource="data" :name="'de'"  class="mt-5">

        <template #cell(name)="{ item: dispatchedEmail }">
            <Link :href="route(dispatchedEmailRoute(dispatchedEmail))">
                {{ dispatchedEmail["name"] }}
            </Link>
        </template>
    </Table>
</template>


