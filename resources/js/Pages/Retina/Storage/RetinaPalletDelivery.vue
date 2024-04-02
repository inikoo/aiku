<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref, watch } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/TableHistories.vue"
import RetinaTablePalletDeliveryPallets from '@/Components/Tables/RetinaTablePalletDeliveryPallets.vue'
import Timeline from '@/Components/Utils/Timeline.vue'
import Popover from '@/Components/Popover.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import { get } from 'lodash'
import UploadExcel from '@/Components/Upload/UploadExcel.vue'
import { trans } from "laravel-vue-i18n"
import { routeType } from '@/types/route'
import { Table } from '@/types/Table'
import { PalletDelivery } from '@/types/Pallet'
import { Tabs as TSTabs } from '@/types/Tabs'
import { PageHeading as PageHeadingTypes } from  '@/types/PageHeading'
import BoxStatsPalletDelivery from "@/Components/Pallet/BoxStatsPalletDelivery.vue"
import { useLayoutStore } from '@/Stores/retinaLayout'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core"
import { faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faCross, faUser, faTruckCouch, faPallet } from '@fal'
library.add(faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faCross, faUser, faTruckCouch, faPallet)

const props = defineProps<{
    title: string
    tabs: TSTabs
    pallets?: Table
    data: {
        data: PalletDelivery
    }
    history?: {}
    pageHead: PageHeadingTypes
    updateRoute: {
        route: routeType
    }
    uploadRoutes: {
        download: routeType
        history: routeType
    }
    storedItemsRoute: {
        index: routeType
        store: routeType
    }
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const loading = ref(false)
const timeline = ref({ ...props.data.data })
const dataModal = ref({ isModalOpen: false })
const formAddPallet = useForm({ notes: '', customer_reference: '' })
const formMultiplePallet = useForm({ number_pallets: 1 })


// Method: Add single pallet
const onAddPallet = (data: {}, closedPopover: Function) => {
    loading.value = true
    formAddPallet.post(route(
        data.route.name,
        data.route.parameters
    ), {
        preserveScroll: true,
        onSuccess: () => {
            closedPopover()
            formAddPallet.reset('notes', 'customer_reference')
            loading.value = false
        },
        onError: (errors) => {
            loading.value = false
            console.error('Error during form submission:', errors)
        },
    })
}

// Method: Add multiple pallet
const onAddMultiplePallet = (data: {}, closedPopover: Function) => {
    loading.value = true
    formMultiplePallet.post(route(
        data.route.name,
        data.route.parameters
    ), {
        preserveScroll: true,
        onSuccess: () => {
            closedPopover()
            formMultiplePallet.reset('number_pallets')
            loading.value = false
        },
        onError: (errors) => {
            loading.value = false
            console.error('Error during form submission:', errors)
        },
    })
}

// Method: Submit pallet
const onSubmitPallet = async (action: { method: any, name: string, parameters: { palletDelivery: number } }) => {
    loading.value = true
    router.post(route(action.name, action.parameters), {}, {
        onError: (e) => {
            console.warn('Error on Submit', e)
        },
        onSuccess: (e) => {
            console.log('on success', e)
            changeTableKey()
        },
        onFinish: (e) => {
            // console.log('11111', e)
            loading.value = false
        }
    })
}

// To re-render Table after click Submit (so the Table retrieve the new props)
const tableKey = ref(1)
const changeTableKey = () => {
    tableKey.value = tableKey.value + 1
}


const component = computed(() => {
    const components = {
        pallets: RetinaTablePalletDeliveryPallets,
        history: TableHistories
    }
    return components[currentTab.value]

})

// Method: Open modal upload
const onUploadOpen = (action) => {
    dataModal.value.isModalOpen = true
    dataModal.value.uploadRoutes = action.route
}

watch(() => props.data, (newValue) => {
    timeline.value = newValue.data
}, { deep: true })

</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <!-- Button: Upload -->
        <template #button-group-upload="{ action }">
            <Button @click="() => onUploadOpen(action.button)" :style="action.button.style" :icon="action.button.icon"
                v-tooltip="action.button.tooltip" class="rounded-l rounded-r-none border-none" />
        </template>

        <!-- Button: Add many pallete -->
        <template #button-group-multiple="{ action }">
            <Popover width="w-full" class="relative h-full">
                <template #button>
                    <Button :style="action.button.style" :icon="action.button.icon" :iconRight="action.button.iconRight"
                        :key="`ActionButton${action.button.label}${action.button.style}`"
                        :tooltip="'Add multiple pallet'" class="rounded-r-none border-none" />
                </template>

                <template #content="{ close: closed }">
                    <div class="w-[250px]">
                        <span class="text-xs px-1 my-2">Number of pallets : </span>
                        <div>
                            <PureInput v-model="formMultiplePallet.number_pallets" placeholder="1" type="number"
                                :minValue="1" autofocus />
                            <p v-if="get(formMultiplePallet, ['errors', 'customer_reference'])"
                                class="mt-2 text-sm text-red-600">
                                {{ formMultiplePallet.errors.number_pallets }}
                            </p>
                        </div>
                        <div class="flex justify-end mt-3">
                            <Button :style="'save'" :loading="loading" :label="'save'"
                                @click="() => onAddMultiplePallet(action.button, closed)" />
                        </div>
                    </div>
                </template>
            </Popover>
        </template>

        <!-- Button: Add pallet (single) -->
        <template #button-group-add-pallet="{ action: action }">
            <div class="relative">
                <Popover width="w-full">
                    <template #button>
                        <Button :style="action.button.style" :label="action.button.label" :icon="action.button.icon"
                            :key="`ActionButton${action.button.label}${action.button.style}`"
                            :tooltip="action.button.tooltip" class="rounded-l-none rounded-r border-none " />
                    </template>

                    <template #content="{ close: closed }">
                        <div class="w-[250px]">
                            <span class="text-xs px-1 my-2">{{ trans('Reference') }}: </span>
                            <div>
                                <PureInput v-model="formAddPallet.customer_reference" placeholder="Reference"
                                    autofocus />
                                <p v-if="get(formAddPallet, ['errors', 'customer_reference'])"
                                    class="mt-2 text-sm text-red-600">
                                    {{ formAddPallet.errors.customer_reference }}
                                </p>
                            </div>

                            <div class="mt-3">
                                <span class="text-xs px-1 my-2">{{ trans('Notes') }}: </span>
                                <textarea v-model="formAddPallet.notes" placeholder="Notes"
                                    class="block w-full rounded-md border-gray-300 shadow-sm placeholder:text-gray-400 focus:border-gray-500 focus:ring-gray-500 sm:text-sm" />
                                <p v-if="get(formAddPallet, ['errors', 'notes'])" class="mt-2 text-sm text-red-600">
                                    {{ formAddPallet.errors.notes }}
                                </p>
                            </div>

                            <div class="flex justify-end mt-3">
                                <Button :style="'save'" :loading="loading" :label="'save'"
                                    @click="() => onAddPallet(action.button, closed)" />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
        </template>

        <!-- Button: Submit -->
        <template #button-submit="{ action: action }">
            <Button @click="onSubmitPallet(action.action.route)" :style="action.action.style"
                :label="action.action.label" :loading="loading" />
        </template>
    </PageHeading>

    <div v-if="timeline.state != 'in-process'" class="border-b border-gray-200">
        <Timeline :options="timeline.timeline" :state="timeline.state" :slidesPerView="6" />
    </div>

    <!-- Box: Stats -->
    <div class="h-16 grid grid-cols-4 gap-x-2 px-4 my-4">
        <BoxStatsPalletDelivery :layout="useLayoutStore()" tooltip="Delivery status" :label="data?.data.state" icon="fal fa-truck-couch" />
        <BoxStatsPalletDelivery :layout="useLayoutStore()" tooltip="Total pallet" :label="pallets?.meta.total" icon="fal fa-pallet" />

        <!-- <div class="relative flex flex-col justify-between p-4 rounded-md bg-fuchsia-200/70 border border-fuchsia-300 overflow-hidden">

        </div> -->
    </div>

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :state="timeline.state" :tab="currentTab" :tableKey="tableKey"
        :storedItemsRoute="storedItemsRoute" @renderTableKey="() => (console.log('emit render', changeTableKey()))" />

    <UploadExcel :propName="'pallet deliveries'" description="Adding Pallet Deliveries" :routes="{
        upload: get(dataModal, 'uploadRoutes', {}),
        download: props.uploadRoutes.download,
        history: props.uploadRoutes.history
    }" :dataModal="dataModal" />

    <!-- <pre>{{ props.pallets }}</pre> -->
    <!-- <pre>{{ $inertia.page.props.queryBuilderProps.pallets.columns }}</pre> -->
</template>
