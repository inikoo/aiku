<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:46:51 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Table from "@/Components/Table/Table.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"
import TagPallet from '@/Components/TagPallet.vue'
import {useFormatTime} from "@/Composables/useFormatTime"
import { PalletDelivery } from "@/types/pallet-delivery"
import Icon from "@/Components/Icon.vue"
import { inject } from "vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"

library.add(faPlus)

const props = defineProps<{
    data: {}
    tab?: string
}>()

const layout = inject('layout', layoutStructure)

function uploadRoutes(upload: {}) {
    switch (route().current()) {
        case 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.index':
            return route(
                'grp.helpers.uploads.records.show',
                [
                    upload.id
                ]);
        default:
            if (route().current().startsWith('retina')) {
                return route(
                    'retina.helpers.uploads.records.show',
                    [
                        upload.id
                    ]);
            } else {
                return route(
                    'grp.helpers.uploads.records.show',
                    [
                        upload.id
                    ]);
            }
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(original_filename)="{ item: upload }">
            <Link :href="uploadRoutes(upload)" class="primaryLink">
                {{ upload['original_filename'] }}
            </Link>
        </template>

        <template #cell(uploaded_at)="{ item: upload }">
            {{ useFormatTime(upload.uploaded_at) }}
        </template>
    </Table>
</template>
