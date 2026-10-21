<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import TablePalletStoredItem from '@/Components/Tables/Grp/Org/Fulfilment/TablePalletStoredItem.vue'
// import TablePallets from '@/Components/Tables/Grp/Org/Fulfilment/TablePallets.vue'
import { routeType } from '@/types/route'
import StoredItemShowcase from '@/Components/Showcases/Grp/StoredItemShowcase.vue'
import TableAttachments from '@/Components/Tables/Grp/Helpers/TableAttachments.vue'
import UploadAttachment from '@/Components/Upload/UploadAttachment.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { ctrans } from '@/Composables/useTrans'
import { Table as TableTS } from '@/types/Table'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faFragile, faNarwhal, faExchange, faYinYang, faBallotCheck, faTruckCouch, faSignOutAlt, faGhost, faUpload } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import StockItemsMovements from '@/Components/Showcases/Grp/StockItemsMovements.vue'
import TableStoredItemsAudits from '@/Components/Tables/Grp/Org/Fulfilment/TableStoredItemsAudits.vue'
import TableStoredItemAuditDeltas from '@/Components/Tables/Grp/Org/Fulfilment/TableStoredItemAuditDeltas.vue'
library.add(faFragile, faNarwhal, faExchange, faYinYang, faBallotCheck, faTruckCouch, faSignOutAlt, faGhost, faUpload)


const props = defineProps<{
    title: string
    tabs: {
        current: string
        navigation: {}
    }
    pallets?: {}
    showcase?: {}
    movements?: {}
    history?: {}
    audits?: {}
    attachments?: TableTS
    attachmentRoutes: {
        attachRoute: routeType
        detachRoute: routeType
    }
    option_attach_file?: {
        name: string
        code: string
    }[]
    pageHead: {}
    palletRoute: {
        index: routeType
    }
    locationRoute: {
        index: routeType
    }
    update: routeType
}>()

let currentTab = ref(props.tabs.current)
const isModalUploadFileOpen = ref(false)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const component = computed(() => {

    const components = {
        showcase: StoredItemShowcase,
        pallets: TablePalletStoredItem,
        history: TableHistories,
        movements: StockItemsMovements,
        audits: TableStoredItemAuditDeltas,
        attachments: TableAttachments
    }
    return components[currentTab.value]

});

</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #other>
            <Button
                v-if="currentTab === 'attachments'"
                @click="() => (isModalUploadFileOpen = true)"
                :label="ctrans('Attach manual')"
                type="tertiary">
                <template #icon>
                    <FontAwesomeIcon icon="fal fa-upload" class="text-green-600" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </template>
    </PageHeading>
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :tab="currentTab" :palletRoute="palletRoute"
        :locationRoute="locationRoute" :updateRoute="update" :detachRoute="attachmentRoutes?.detachRoute">
        <template #button-empty-state-attachments>
            <Button
                v-if="currentTab === 'attachments'"
                @click="() => (isModalUploadFileOpen = true)"
                :label="ctrans('Attach manual')"
                type="tertiary">
                <template #icon>
                    <FontAwesomeIcon icon="fal fa-upload" class="text-green-600" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </template>
    </component>

    <UploadAttachment
        v-model="isModalUploadFileOpen"
        scope="attachment"
        :title="{
            label: ctrans('Upload the manual of this product'),
            information: ctrans('PDF only. It is printed with the parcel documents of every return with this product.'),
        }"
        :attachmentRoutes
        :options="props.option_attach_file" />
</template>
