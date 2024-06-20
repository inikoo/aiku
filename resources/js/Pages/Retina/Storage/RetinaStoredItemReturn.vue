<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head} from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref, watch } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import Timeline from "@/Components/Utils/Timeline.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Modal from "@/Components/Utils/Modal.vue"
import { routeType } from '@/types/route'
import { PageHeading as PageHeadingTypes } from  '@/types/PageHeading'
import StoredItemReturnDescriptor from "@/Components/PalletReturn/Descriptor/StoredItemReturn"
import TableReturn from '@/Components/PalletReturn/tablePalletReturn.vue'
import TableStoredItemReturnStoredItems from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItemReturnStoredItems.vue";
import FieldEditableTable from "@/Components/FieldEditableTable.vue"

const props = defineProps<{
    title: string
    tabs: object
    items?: object
    data?: object
    history?: object
    pageHead: PageHeadingTypes
    updateRoute: routeType
    uploadRoutes: routeType
    storedItemRoute: {
        index: routeType,
        store: routeType
    }
}>()
let currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab)
const timeline = ref({ ...props.data.data })
const openModal = ref(false)

const component = computed(() => {
    const components = {
        items: TableStoredItemReturnStoredItems,
        history: TableHistories,
    }
    return components[currentTab.value]
})

const onFilterDatalist=(data)=>{
    return data.filter((item)=>item.total_quantity > 0)
}


const submitDataStoredItem = (formData, list) => {
    const finalValue = {};
    for (let v of formData) {
        const dataSelected = list.find((item) => item.id == v);
        finalValue[v] = { quantity : dataSelected.total_quantity}
    }
    return finalValue;
};


watch(
    props,
    (newValue) => {
        timeline.value = newValue.data.data
    },
    { deep: true }
)


</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-group-add-stored-item="{ action }">
            <Button :style="action.style" :label="action.label" :icon="action.icon"
                    :iconRight="action.iconRight" :key="`ActionButton${action.label}${action.style}`"
                    :tooltip="action.tooltip" @click="() => (openModal = true)" />
        </template>
    </PageHeading>

    <div class="border-b border-gray-200">
        <Timeline :options="timeline.timeline" :state="timeline.state" :slidesPerView="Object.entries(timeline.timeline).length" />
    </div>

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :state="timeline.state" :tab="currentTab" />

    <Modal :isOpen="openModal" @onClose="openModal = false">
        <div class="min-h-72 max-h-96 px-2 overflow-auto">
            <TableReturn
                :dataRoute="storedItemRoute.index"
                :saveRoute="storedItemRoute.store"
                @onClose="() => openModal = false"
                :descriptor="StoredItemReturnDescriptor"
                :onFilterDatalist="onFilterDatalist"
                :beforeSubmit="submitDataStoredItem"
            >
            <template #column-quantity="{ data : dataColumn }">
                <FieldEditableTable 
                    :data="dataColumn.columnData"  
                    fieldName="total_quantity" 
                    placeholder="Enter pallet Quantity"
                    type="number"
                    @input="(e)=>dataColumn.columnData.total_quantity = e"
                    :min="1"
                    :max="dataColumn.columnData.max_quantity"
                />
              </template>
            </TableReturn>
        </div>
    </Modal>
</template>
