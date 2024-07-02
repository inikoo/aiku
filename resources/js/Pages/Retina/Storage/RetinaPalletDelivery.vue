<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref, watch, inject } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/Grp/Helpers/TableHistories.vue"
import Timeline from '@/Components/Utils/Timeline.vue'
import Popover from '@/Components/Popover.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import { get } from 'lodash'
import UploadExcel from '@/Components/Upload/UploadExcel.vue'
import { trans } from "laravel-vue-i18n"
import { routeType } from '@/types/route'
import { Table } from '@/types/Table'
import { PalletDelivery, PDBoxStats } from '@/types/Pallet'
import { Tabs as TSTabs } from '@/types/Tabs'
import { PageHeading as PageHeadingTypes } from  '@/types/PageHeading'
import BoxStatPallet from "@/Components/Pallet/BoxStatPallet.vue"
import DatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { useFormatTime } from '@/Composables/useFormatTime';
import axios from 'axios'
import { notify } from '@kyvg/vue3-notification'

import RetinaTablePalletDeliveryPallets from '@/Components/Tables/Retina/RetinaTablePalletDeliveryPallets.vue'
import TableServices from "@/Components/Tables/Grp/Org/Fulfilment/TableServices.vue"
import TablePhysicalGoods from "@/Components/Tables/Grp/Org/Fulfilment/TablePhysicalGoods.vue"
import TableStoredItems from "@/Components/Tables/Grp/Org/Fulfilment/TableStoredItems.vue"

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core"
import { faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faCross, faUser, faTruckCouch, faPallet, faCalendarDay, faConciergeBell, faCube, faSortSizeUp, faBox } from '@fal'
library.add(faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faCross, faUser, faTruckCouch, faPallet, faCalendarDay, faConciergeBell, faCube, faSortSizeUp, faBox)

const props = defineProps<{
    title: string
    tabs: TSTabs
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
    box_stats: PDBoxStats

    pallets?: Table
    stored_items?: Table
    services?: Table
    physical_goods?: Table
}>()

const layout = inject('layout', {})

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)
const isLoading = ref<string | boolean>(false)
const timeline = ref({ ...props.data.data })
const dataModal = ref({ isModalOpen: false })
const formAddPallet = useForm({ notes: '', customer_reference: '', type : 'pallet' })
const formMultiplePallet = useForm({ number_pallets: 1, type : 'pallet' })


// Method: Add single pallet
const onAddPallet = (data: {}, closedPopover: Function) => {
    isLoading.value = 'addSinglePallet'
    formAddPallet.post(route(
        data.route.name,
        data.route.parameters
    ), {
        preserveScroll: true,
        onSuccess: () => {
            closedPopover()
            formAddPallet.reset('notes', 'customer_reference','type')
            isLoading.value = false
        },
        onError: (errors) => {
            isLoading.value = false
            console.error('Error during form submission:', errors)
        },
    })
}



const onChangeEstimateDate = async () => {
    try {
        const response = await axios.patch(route(props.updateRoute.route.name, props.updateRoute.route.parameters), {
            estimated_delivery_date : props.data.data.estimated_delivery_date
        })
    } catch (error) {
        console.log(error)
        notify({
			title: "Failed",
			text: "Failed to update the Delivery date, try again.",
			type: "error",
		})
    }
}


// Method: Add multiple pallet
const onAddMultiplePallet = (data: {}, closedPopover: Function) => {
    isLoading.value = 'addMultiplePallet'
    formMultiplePallet.post(route(
        data.route.name,
        data.route.parameters
    ), {
        preserveScroll: true,
        onSuccess: () => {
            closedPopover()
            formMultiplePallet.reset('number_pallets','type')
            isLoading.value = false
        },
        onError: (errors) => {
            isLoading.value = false
            console.error('Error during form submission:', errors)
        },
    })
}

// Method: Submit pallet
const onSubmitPallet = async (action: routeType) => {
    isLoading.value = 'submitPallet'
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
            isLoading.value = false
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
        stored_items: TableStoredItems,
        services: TableServices,
        physical_goods: TablePhysicalGoods,
        history: TableHistories
    }
    return components[currentTab.value]

})

// Method: Open modal upload
const onUploadOpen = (action) => {
    dataModal.value.isModalOpen = true
    dataModal.value.uploadRoutes = action.route
}

const changePalletType=(form,fieldName,value)=>{
    form[fieldName] = value
}

const disableBeforeToday=(date)=>{
      const today = new Date();
      // Set time to 00:00:00 for comparison purposes
      today.setHours(0, 0, 0, 0);
      return date < today;
    }


watch(() => props.data, (newValue) => {
    timeline.value = newValue.data
}, { deep: true })

watch(() => props.data?.data.estimated_delivery_date, (newValue) => {
    onChangeEstimateDate()
}, { deep: true })

const typePallet = [
    { label : 'Pallet', value : 'pallet'}, 
    { label : 'Box', value : 'box'}, 
    { label : 'Oversize', value : 'oversize'}
]

</script>

<template>

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <!-- Button: Upload -->
        <template #button-group-upload="{ action }">
            <Button @click="() => onUploadOpen(action)" :style="action.style" :icon="action.icon"
                v-tooltip="action.tooltip" class="rounded-l rounded-r-none border-none" />
        </template>

        <!-- Button: Add many pallets -->
        <template #button-group-multiple="{ action }">
            <Popover width="w-full" class="relative h-full">
                <template #button>
                    <Button :style="action.style" :icon="action.icon" :iconRight="action.iconRight"
                        :key="`ActionButton${action.label}${action.style}`"
                        :tooltip="trans('Add multiple pallets')" class="rounded-r-none border-none" />
                </template>

                <template #content="{ close: closed }">
                    <div class="w-[350px]">
                        <span class="text-xs  my-2">{{ trans('Type') }}: </span>
                        <div class="flex items-center">
                            <div v-for="(typeData, typeIdx) in typePallet" :key="typeIdx" class="relative py-3 mr-4">
                                <div>
                                    <input type="checkbox" :id="typeData.value" :value="typeData.value"
                                        :checked="formMultiplePallet.type == typeData.value"
                                        @input="changePalletType(formMultiplePallet,'type',typeData.value)"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                    <label :for="typeData.value" class="ml-2">{{ typeData.label }}</label>
                                </div>
                            </div>
                        </div>
                        <span class="text-xs px-1 my-2">Number of pallets : </span>
                        <div>
                            <PureInput v-model="formMultiplePallet.number_pallets" placeholder="1-100" type="number"
                                :minValue="1" :maxValue="100" autofocus
                                @update:modelValue="() => formMultiplePallet.errors.number_pallets = ''"
                                @keydown.enter="() => formMultiplePallet.number_pallets ? onAddMultiplePallet(action, closed) : ''" />
                            <p v-if="get(formMultiplePallet, ['errors', 'customer_reference'])"
                                class="mt-2 text-sm text-red-600">
                                {{ formMultiplePallet.errors.number_pallets }}
                            </p>
                        </div>
                        <div class="flex justify-end mt-3">
                            <Button :style="'save'" :loading="isLoading === 'addMultiplePallet'" label="save"
                                @click="() => onAddMultiplePallet(action, closed)" />
                        </div>
                    </div>
                </template>
            </Popover>
        </template>

        <!-- Button: Add pallet (single) -->
        <template #button-group-add-pallet="{ action }">
            <div class="relative">
                <Popover width="w-full">
                    <template #button>
                        <Button :style="action.style" :label="action.label" :icon="action.icon"
                            :key="`ActionButton${action.label}${action.style}`"
                            :tooltip="action.tooltip" class="rounded-l-none rounded-r border-none " />
                    </template>

                    <template #content="{ close: closed }">
                        <div class="w-[350px]">
                            <span class="text-xs px-1 my-2">{{ trans('Type') }}: </span>
                            <div class="flex items-center">
                                <div v-for="(typeData, typeIdx) in typePallet" :key="typeIdx"
                                    class="relative py-3 mr-4">
                                    <div>
                                        <input type="checkbox" :id="typeData.value" :value="typeData.value"
                                            :checked="formAddPallet.type == typeData.value"
                                            @input="changePalletType(formAddPallet, 'type', typeData.value)"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <label :for="typeData.value" class="ml-2">{{ typeData.label }}</label>
                                    </div>
                                </div>
                            </div>
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
                                <Button
                                    :style="'save'"
                                    :loading="isLoading === 'addSinglePallet'"
                                    :label="'save'"
                                    @click="() => onAddPallet(action, closed)"
                                />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
        </template>

        <!-- Button: Submit -->
        <template #button-submit="{ action }">
            <Button @click="onSubmitPallet(action.route)" :style="action.style"
                :label="action.label" :loading="isLoading === 'submitPallet'" />
        </template>
    </PageHeading>

    <!-- Section: Timeline -->
    <div v-if="timeline.state != 'in-process'" class="border-b border-gray-200">
        <Timeline :options="timeline.timeline" :state="timeline.state" :slidesPerView="6" />
    </div>

    <!-- Box: Stats -->
    <div class="h-min grid grid-cols-4 border-b border-gray-200 divide-x divide-gray-300">
        <!-- Box: Status -->
        <BoxStatPallet :color="{bgColor: layout.app.theme[0], textColor: layout.app.theme[1]}"
            class=" pb-2 py-5 px-3" :tooltip="trans('Detail')" :label="capitalize(data?.data.state)"
            icon="fal fa-truck-couch">
            <div class="flex items-center w-full flex-none gap-x-2 mb-2">
                <dt class="flex-none">
                    <span class="sr-only">{{ box_stats.delivery_status.tooltip }}</span>
                    <FontAwesomeIcon :icon='box_stats.delivery_status.icon' :class='box_stats.delivery_status.class'
                        fixed-width aria-hidden='true' />
                </dt>
                <dd class="text-xs text-gray-500">{{ box_stats.delivery_status.tooltip }}</dd>
            </div>

            <div  class="flex items-center w-full flex-none gap-x-2">
                <dt class="flex-none">
                    <span class="sr-only">{{ box_stats.delivery_status.tooltip }}</span>
                    <FontAwesomeIcon :icon="['fal', 'calendar-day']"  :class='box_stats.delivery_status.class'
                        fixed-width aria-hidden='true' />
                </dt>
                <div v-if="(box_stats.delivery_status.tooltip == 'Received' || box_stats.delivery_status.tooltip == 'Booking in' || box_stats.delivery_status.tooltip == 'Booked In')">
                     <dd class="text-xs text-gray-500">{{ data.data.estimated_delivery_date ? useFormatTime(data.data.estimated_delivery_date) : 'Not Set' }}</dd>
                </div>
                <Popover v-else position="">
                    <template #button>
                        <dd class="text-xs text-gray-500">{{ data.data.estimated_delivery_date ? useFormatTime(data.data.estimated_delivery_date) : 'Not Set' }}</dd>
                    </template>
                    <template #content="{ close: closed }">
                        <div>
                            <DatePicker v-model="data.data.estimated_delivery_date" 
                                inline auto-apply  :disabled-dates="disableBeforeToday"  
                                :enable-time-picker="false"
                            />
                        </div>
                    </template>
                </Popover>
            </div>
        </BoxStatPallet>

        <!-- Box: Pallet -->
        <BoxStatPallet :color="{bgColor: layout.app.theme[0], textColor: layout.app.theme[1]}"
            class=" pb-2 py-5 px-3" :tooltip="trans('Pallets')" :percentage="0">
            <div class="flex items-end gap-x-3">
                <dt class="flex-none">
                    <span class="sr-only">Total pallet</span>
                    <FontAwesomeIcon icon='fal fa-pallet' size="xs" class='text-gray-400' fixed-width
                        aria-hidden='true' />
                </dt>
                <dd class="text-gray-600 leading-none text-3xl font-medium">{{ data?.data.number_pallets }}</dd>
            </div>
        </BoxStatPallet>
    </div>

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component :is="component" :data="props[currentTab]" :state="timeline.state" :key="timeline.state" :tab="currentTab"
        :tableKey="tableKey" :storedItemsRoute="storedItemsRoute"
        @renderTableKey="() => (console.log('emit render', changeTableKey()))" />

    <UploadExcel :propName="'pallet deliveries'" description="Adding Pallet Deliveries" :routes="{
        upload: get(dataModal, 'uploadRoutes', {}),
        download: props.uploadRoutes.download,
        history: props.uploadRoutes.history
    }" :dataModal="dataModal" />

    <!-- <pre>{{ props.pallets }}</pre> -->
    <!-- <pre>{{ $inertia.page.props.queryBuilderProps.pallets.columns }}</pre> -->
</template>
