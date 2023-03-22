<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Mailshot} from "@/types/mailshot";

const props = defineProps<{
    data: object
}>()


function mailshotRoute(mailshot: Mailshot) {
    switch (route().current()) {
        case 'mail.mailshots.index':
            return route(
                'mail.mailshots.show',
                [mailshot.data, mailshot.state]);
        default:
            return route(
                'mailshots.show',
                [mailshot.outbox_id]);
    }
}



</script>

<template>


    <Table :resource="data" :name="'ms'" class="mt-5">
        <template #cell(name)="{ item: mailshot }">
            <Link :href="route(mailshotRoute(mailshot))">
                {{ mailshot["name"] }}
            </Link>
        </template>
    </Table>
</template>


