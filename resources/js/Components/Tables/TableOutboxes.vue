<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Outbox} from "@/types/outbox";

const props = defineProps<{
    data: object,
    tab?: string
}>()


function outboxRoute(outbox: Outbox) {
    switch (route().current()) {
        case 'grp.org.shops.show.mail.outboxes':
        return route(
                'grp.org.shops.show.mail.outboxes.show',
                [route().params['organisation'], route().params['shop'], outbox.slug])
        default:
            return null
    }
}



</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">


        <template #cell(name)="{ item: outbox }">
            <Link :href="outboxRoute(outbox)">
                {{ outbox["name"] }}
            </Link>
        </template>

    </Table>
</template>


