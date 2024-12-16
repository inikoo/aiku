<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import {Link} from '@inertiajs/vue3';
import Table from '@/Components/Table/Table.vue';
import {Mailshot} from "@/types/mailshot";
import icon from '@/Components/Icon.vue'
import { faSpellCheck, faSeedling, faPaperPlane, faStop } from '@fal'
import { library } from "@fortawesome/fontawesome-svg-core";
library.add(faSpellCheck, faSeedling, faPaperPlane, faStop )

const props = defineProps<{
    data: object,
    tab?: string
}>()

function emailBulkRunRoutes(emailBulkRun: {}) {
    switch (route().current()) {
        case 'grp.org.shops.show.comms.outboxes.show':
        return route(
                'grp.org.shops.show.comms.outboxes.show.email-bulk-runs.show',
                [route().params['organisation'], route().params['shop'], route().params['outbox'], emailBulkRun.id])
        default:
            return null
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
      <template #cell(subject)="{ item: emailBulkRun }">
            <Link :href="emailBulkRunRoutes(emailBulkRun)" class="primaryLink">
                {{ emailBulkRun["subject"] }}
            </Link>
        </template>
    </Table>
</template>


