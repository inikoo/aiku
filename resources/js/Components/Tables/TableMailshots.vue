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
    data: object,
    tab?: string
}>()


function mailshotRoute(mailshot: Mailshot) {
    switch (route().current()) {
        case 'grp.org.shops.show.marketing.mailshots.index':
            return route(
                'grp.org.shops.show.marketing.mailshots.show',
                [route().params.organisation, route().params.shop, mailshot.id]);
        default:
            return route(
                'mailshots.show',
                [mailshot.outbox_id]);
    }
}



</script>

<template>

    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(id)="{ item: mailshot }">
            <Link :href="mailshotRoute(mailshot)">
                {{ mailshot["id"] }}
            </Link>
        </template>
    </Table>
</template>


