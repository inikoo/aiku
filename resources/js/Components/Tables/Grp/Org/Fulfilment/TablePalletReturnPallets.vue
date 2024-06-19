<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:46:51 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue"
import { Link } from "@inertiajs/vue3"
import Tag from "@/Components/Tag.vue"
import TagPallet from '@/Components/TagPallet.vue'
import '@/Composables/Icon/PalletReturnStateEnum'  // Import all icon for State

import Icon from "@/Components/Icon.vue"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { inject, reactive, ref } from 'vue'
import { trans } from "laravel-vue-i18n"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import Popover from '@/Components/Popover.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTrashAlt, faPaperPlane } from "@far"
import { faSignOutAlt, faTimes, faShare, faCross, faUndo } from "@fal"
import PureTextarea from "@/Components/Pure/PureTextarea.vue"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"

const layout = inject('layout', layoutStructure)

library.add(faTrashAlt, faSignOutAlt, faTimes, faShare, faCross, faUndo, faPaperPlane)

const props = defineProps<{
    data: {}
    tab?: string
    state?: string
    app?: string // 'retina'
}>()

// Not Picked
const listStatusNotPicked = [
    {
        label: trans('Damaged'),
        value: 'damaged'
    },
    {
        label: trans('Lost'),
        value: 'lost'
    },
    {
        label: trans('Other'),
        value: 'other'
    }
] 
const selectedStatusNotPicked = reactive({
    status: 'other',
    message: ''
})
const errorNotPicked = reactive({
    status: null,
    message: null
})
const isSubmitNotPickedLoading = ref<boolean | number>(false)
const onSubmitNotPicked = async (idPallet: number, closePopup: Function) => {
    isSubmitNotPickedLoading.value = idPallet

    setTimeout(() => {
        selectedStatusNotPicked.status = 'other'
        selectedStatusNotPicked.message = ''
        errorNotPicked.status = null
        errorNotPicked.message = null
        isSubmitNotPickedLoading.value = false
        closePopup()
    }, 1000)
}


const isDeleteLoading = ref<boolean | string>(false)
const isPickingLoading = ref(false)
const isUndoLoading = ref(false)
</script>

<template>
    <!-- <pre>{{data}}</pre> -->
    <Table :resource="data" :name="tab" class="mt-5">

        <!-- Column: Type Icon -->
		<template #cell(type_icon)="{ item: palletDelivery }">

            <!-- Icon: Type -->
            <div v-if="app == 'retina'" class="px-3" />
            <FontAwesomeIcon v-else v-tooltip="palletDelivery.type_icon.tooltip" :icon='palletDelivery.type_icon.icon' :class='palletDelivery.type_icon.class' fixed-width aria-hidden='true' />

           <!-- Icon: State -->
            <div v-if="app == 'retina'" class="px-3">
                <TagPallet :stateIcon="palletDelivery.state_icon" />
            </div>
			<Icon v-else :data="palletDelivery['state_icon']" class="px-1" />

		</template>

        <!-- Column: State -->
		<!-- <template #cell(state)="{ item: palletDelivery }">
            <div v-if="app == 'retina'" class="px-3">
                <TagPallet :stateIcon="palletDelivery.state_icon" />
            </div>
			<Icon v-else :data="palletDelivery['state_icon']" class="px-1" />
		</template> -->

        <!-- Column: Stored Items -->
        <template #cell(stored_items)="{ item: pallet }">
            <div v-if="pallet.stored_items.length" class="flex flex-wrap gap-x-1 gap-y-1.5">
                <Tag v-for="item of pallet.stored_items" :theme="item.id" :label="`${item.reference} (${item.quantity})`" :closeButton="false"
                    :stringToColor="true">
                    <template #label>
                        <div class="whitespace-nowrap text-xs">
                            {{ item.reference }} (<span class="font-light">{{ item.quantity }}</span>)
                        </div>
                    </template>
                </Tag>
            </div>

            <div v-else class="text-gray-400 text-xs italic">
                No items in this pallet
            </div>
        </template>

        <!-- Column: Location -->
		<template #cell(location)="{ item: palletDelivery }">
            {{ palletDelivery.location_slug }}
		</template>

        <!-- Column: Actions -->
        <template #cell(actions)="{ item: pallet }" v-if="props.state == 'in-process' || props.state == 'picking'">
            <!-- <pre>{{ pallet.state }}</pre> -->
            
            <div v-if="props.state == 'in-process'">
                <Link as="div" :href="route(pallet.deleteFromReturnRoute.name, pallet.deleteFromReturnRoute.parameters)" v-tooltip="trans('Unselect this pallet')" method="delete"
                    @start="() => isDeleteLoading = pallet.id"
                    @finish="() => isDeleteLoading = false"
                >
                    <Button icon="fal fa-times" type="negative" :loading="pallet.id === isDeleteLoading" />
                </Link>
            </div>

            <!-- State: Pick or not-picked -->
            <div v-if="props.state == 'picking' && layout.app.name == 'Aiku'" class="flex gap-x-2 ">
                <!-- {{ pallet.state }} -->
                <!-- Button: Picking -->
                <Link v-if="pallet.state !== 'picked'" as="div"
                    :href="route(pallet.updateRoute.name, pallet.updateRoute.parameters)"
                    :data="{ state: 'picked' }"
                    @start="() => isPickingLoading = pallet.id"
                    @finish="() => isPickingLoading = false"
                    method="patch"
                    v-tooltip="`Set as picked`"    
                >
                    <!-- <div class="border border-green-500 rounded py-2 px-6 hover:bg-green-500/10 cursor-pointer">
                        <FontAwesomeIcon icon='fal fa-check' class='flex items-center justify-center text-green-500' fixed-width aria-hidden='true' />
                    </div> -->
                    <Button icon="fal fa-check" type="positive" :loading="isPickingLoading === pallet.id" class="py-0" />
                </Link>

                <!-- Button: Undo picking -->
                <Link v-if="pallet.state === 'picked'" as="div"
                    :href="route(pallet.updateRoute.name, pallet.updateRoute.parameters)"
                    :data="{ state: 'picked' }"
                    @start="() => isUndoLoading = pallet.id"
                    @finish="() => isUndoLoading = false"
                    method="patch"
                    v-tooltip="`Undo`"    
                >
                    <Button icon="fal fa-undo" label="Undo picking" type="tertiary" size="xs" :loading="isUndoLoading === pallet.id" class="py-0" />
                </Link>

                <!-- Button: Set as not picked -->
                <Popover v-if="pallet.state === 'picking'" width="w-full">
                    <template #button="{ open }">
                        <Button icon="fal fa-times"
                            v-tooltip="trans('Set as not picked')"
                            :type="'negative'"
                            :key="pallet.id + open"
                            :loading="isSubmitNotPickedLoading == pallet.id"
                        />
                    </template>

                    <template #content="{ close }">
                        <div class="w-[250px]">
                            <!-- Field: Status -->
                            <div class="mb-3">
                                <div class="text-xs px-1 mb-1"><span class="text-red-500 text-sm mr-0.5">*</span>Select status: </div>
                                <PureMultiselect v-model="selectedStatusNotPicked.status" @update:modelValue="() => errorNotPicked.status = null" :options="listStatusNotPicked" required caret :class="errorNotPicked.status ? 'errorShake' : ''" />
                                <div v-if="errorNotPicked.status" class="mt-1 text-red-500 italic text-xxs">{{ errorNotPicked.status }}</div>
                            </div>

                            <!-- Field: Description -->
                            <div class="mb-4 ">
                                <div class="text-xs px-1 mb-1"><span class="text-red-500 text-sm mr-0.5">*</span>Description:</div>
                                <PureTextarea v-model="selectedStatusNotPicked.message" @update:modelValue="() => errorNotPicked.message = null" placeholder="Enter reason why the pallet is not picked" :class="errorNotPicked.message ? 'errorShake' : ''" />
                                <div v-if="errorNotPicked.message" class="mt-1 text-red-500 italic text-xxs">{{ errorNotPicked.message }}</div>
                            </div>

                            <!-- Button: Save -->
                            <div class="flex justify-end mt-2">
                                <Button @click="async () => onSubmitNotPicked(pallet.id, close)"
                                    full
                                    label="Submit"
                                    :disabled="!selectedStatusNotPicked.status || !selectedStatusNotPicked.message"
                                    :loading="isSubmitNotPickedLoading == pallet.id"
                                />
                            </div>
                        </div>
                    </template>
                </Popover>
            </div>
        </template>


    </Table>
</template>
