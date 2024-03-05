<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Mon, 17 Oct 2022 17:33:07 British Summer Time, Sheffield, UK
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import { capitalize } from "@/Composables/capitalize"
import Tabs from "@/Components/Navigation/Tabs.vue"
import { computed, ref, watch } from "vue"
import { useTabChange } from "@/Composables/tab-change"
import TableHistories from "@/Components/Tables/TableHistories.vue"
import TablePalletDeliveryPallets from '@/Components/Tables/TablePalletDeliveryPallets.vue'
import Timeline from '@/Components/Utils/Timeline.vue'
import Popover from '@/Components/Popover.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import PureInput from '@/Components/Pure/PureInput.vue'
import { get } from 'lodash'
import UploadExcel from '@/Components/Upload/UploadExcel.vue'
import { trans } from "laravel-vue-i18n"
import { routeType } from '@/types/route'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core"
import { faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faUser, faTruckCouch, faPallet, faPlus } from '@fal'
library.add(faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faUser, faTruckCouch, faPallet, faPlus)


const props = defineProps<{
    title: string
    tabs: {}
    pallets?: {
        data: {
            customer_name: string
        }[]
        meta: {
            total: number
        }
    }
    data?: {
        data: {
            state: string
        }
    }
    history?: {}
    pageHead: {}
    updateRoute: {
        route: routeType
    }
    uploadRoutes: {},
    locationRoute : {},
    storedItemsRoute : {},
}>()

const currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug) => useTabChange(tabSlug, currentTab)
const loading = ref(false)
const timeline = ref({ ...props.data.data })
const dataModal = ref({ isModalOpen: false })
const formAddPallet = useForm({ notes: '', customer_reference: '' })
const formMultiplePallet = useForm({ number_pallets: 1 })

const handleFormSubmitAddPallet = (data: object, closedPopover: Function) => {
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

const handleFormSubmitAddMultiplePallet = (data: object, closedPopover: Function) => {
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

/* const updateState = async ({ step, options }) => {

  const foundState = options.find((item) => item.key === timeline.value.state)
  const set = step.key == timeline.state || step.index < foundState.index
  if (!set) {
    try {
      const response = await axios.patch(
        route(props.updateRoute.route.name, props.updateRoute.route?.parameters),
        { state: get(step, 'key') }
      )
      console.log(response)
      timeline.value = response.data.data
    } catch (error) {
      console.log('error', error)
    }
  }
} */

const tableKey = ref(1)  // To re-render Table after click Confirm (so the Table retrieve the new props)

// Button: Confirm
const handleClickConfirm = async (action: { method: any, name: string, parameters: { palletDelivery: number } }) => {
    loading.value = true
    router.post(route(action.name, action.parameters), {}, {
        onError: (e) => {
            console.warn('Error on confirm', e)
        },
        onSuccess: (e) => {
            // console.log('on success', e)
            changeTableKey()
        },
        onFinish: (e) => {
            // console.log('11111', e)
            loading.value = false
        }
    })
}

const changeTableKey = () => {
    tableKey.value = tableKey.value + 1
}

const component = computed(() => {
    const components = {
        pallets: TablePalletDeliveryPallets,
        history: TableHistories
    }
    return components[currentTab.value]

})

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
            <Button @click="() => onUploadOpen(action.button)"
                :style="action.button.style"
                :icon="action.button.icon"
                v-tooltip="action.button.tooltip"
                class="rounded-l rounded-r-none border-none" />
        </template>

        <!-- Button: Add many pallete -->
        <template #button-group-multiple="{ action }">
            <Popover width="w-full" class="h-full">
                <template #button>
                    <Button :style="action.button.style" :icon="action.button.icon" :iconRight="action.button.iconRight"
                        :key="`ActionButton${action.button.label}${action.button.style}`"
                        :tooltip="'Add multiple pallet'"
                        class="rounded-none border-none" />
                </template>
                <template #content="{ close: closed }">
                    <div class="w-[250px]">
                        <span class="text-xs px-1 my-2">Number of pallets : </span>
                        <div>
                            <PureInput v-model="formMultiplePallet.number_pallets" placeholder="number of pallets" type="number" :minValue="1" />
                            <p v-if="get(formMultiplePallet, ['errors', 'customer_reference'])" class="mt-2 text-sm text-red-600">
                                {{ formMultiplePallet.errors.number_pallets }}
                            </p>
                        </div>
                        <div class="flex justify-end mt-3">
                            <Button :style="'save'" :loading="loading" :label="'save'"
                                @click="() => handleFormSubmitAddMultiplePallet(action.button, closed)" />
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
                        <Button :style="action.button.style"
                            :label="action.button.label"
                            :icon="action.button.icon"
                            :key="`ActionButton${action.button.label}${action.button.style}`"
                            :tooltip="action.button.tooltip"
                            class="rounded-l-none rounded-r border-none " />
                    </template>
                    
                    <template #content="{ close: closed }">
                        <div class="w-[250px]">
                            <span class="text-xs px-1 my-2">{{ trans('Reference') }}: </span>
                            <div>
                                <PureInput v-model="formAddPallet.customer_reference" placeholder="Reference" />
                                <p v-if="get(formAddPallet, ['errors', 'customer_reference'])"
                                    class="mt-2 text-sm text-red-600">
                                    {{ formAddPallet.errors.customer_reference }}
                                </p>
                            </div>

                            <div class="mt-3">
                                <span class="text-xs px-1 my-2">{{ trans('Notes') }}: </span>
                                <textarea
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    v-model="formAddPallet.notes" placeholder="Notes">
                                </textarea>
                                <p v-if="get(formAddPallet, ['errors', 'notes'])" class="mt-2 text-sm text-red-600">
                                    {{ formAddPallet.errors.notes }}
                                </p>
                            </div>

                            <div class="flex justify-end mt-3">
                                <Button :style="'save'" :loading="loading" :label="'save'" @click="() => handleFormSubmitAddPallet(action.button, closed)" />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
        </template>

        <!-- Button: Confirm -->
        <template #button-confirm="{ action: action }">
            <div>
                <!-- <Link as="Button" :style="action.action.style"
                    :label="action.action.label"
                    :loading="loading" :href="route(action.action.route.name, action.action.route.parameters)" method="post">
                    <font-awesome-icon class="text-red-600" :icon="['far', 'trash-alt']" />
                </Link> -->
                <Button @click="handleClickConfirm(action.action.route)" :style="action.action.style"
                    :label="action.action.label" :loading="loading" />
            </div>
        </template>
    </PageHeading>

    <div v-if="timeline.state != 'in-process'" class="border-b border-gray-200">
        <Timeline :options="timeline.timeline" :state="timeline.state" :slidesPerView="5" />
    </div>

    <!-- Box -->
    <div class="h-16 grid grid-cols-4 gap-x-2 px-6 my-4">
        <!-- Stats: User name -->
        <div v-tooltip="'Customer name'"
            class="relative flex flex-col justify-center p-4 rounded-md bg-slate-100 border border-slate-300 overflow-hidden">
            <!-- <div class="text-zinc-500">User name</div> -->
            <div class="text-2xl font-bold">{{ pallets?.data[0]?.customer_name }}</div>
            <FontAwesomeIcon icon='fal fa-user' class='text-zinc-800/30 absolute text-[40px] right-2' fixed-width
                aria-hidden='true' />
        </div>

        <!-- Stats: Delivery Status -->
        <div v-tooltip="'Delivery status'"
            class="relative flex flex-col justify-center px-4 rounded-md bg-slate-100 border border-slate-300 overflow-hidden">
            <!-- <div class="text-gray-500">Delivery status</div> -->
            <div class="text-2xl font-bold capitalize leading-none">{{ data?.data.state }}</div>
            <FontAwesomeIcon icon='fal fa-truck-couch' class='text-zinc-800/30 absolute text-[40px] right-2' fixed-width
                aria-hidden='true' />
        </div>

        <!-- Stats: Pallet count -->
        <div v-tooltip="'Total pallet'"
            class="relative flex flex-col justify-center p-4 rounded-md bg-slate-100 border border-slate-300 overflow-hidden">
            <!-- <div class="text-gray-500">Number of pallets</div> -->
            <div class="text-2xl font-bold capitalize">{{ pallets?.meta.total }}</div>
            <FontAwesomeIcon icon='fal fa-pallet' class='text-zinc-800/30 absolute text-[40px] right-2' fixed-width
                aria-hidden='true' />
        </div>

        <!-- <div class="relative flex flex-col justify-between p-4 rounded-md bg-fuchsia-200/70 border border-fuchsia-300 overflow-hidden">

        </div> -->
    </div>

    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" />
    <component
        :is="component"
        :data="props[currentTab]"
        :state="timeline.state"
        :tab="currentTab"
        :tableKey="tableKey"
        @renderTableKey="changeTableKey"
        :locationRoute="locationRoute" 
        :storedItemsRoute="storedItemsRoute"
    />

    <UploadExcel :propName="'pallet deliveries'" description="Adding Pallet Deliveries" :routes="{
        upload: get(dataModal, 'uploadRoutes', {}),
        download: props.uploadRoutes.download,
        history: props.uploadRoutes.history
    }" :dataModal="dataModal" />

    <!-- <pre>{{ props.pallets.data?.[0]?.reference }}</pre>
    <pre>{{ $inertia.page.props.queryBuilderProps.pallets.columns }}</pre> -->
</template>
