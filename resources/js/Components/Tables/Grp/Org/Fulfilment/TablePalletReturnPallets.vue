<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 19 May 2024 18:46:51 British Summer Time, Sheffield, UK
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue"
import { Link, router } from "@inertiajs/vue3"
import Tag from "@/Components/Tag.vue"
import TagPallet from '@/Components/TagPallet.vue'
import '@/Composables/Icon/PalletReturnStateEnum'  // Import all icon for State

import Icon from "@/Components/Icon.vue"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { inject, reactive, ref, onBeforeMount } from 'vue'
import { trans } from "laravel-vue-i18n"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import Popover from '@/Components/Popover.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTrashAlt, faPaperPlane } from "@far"
import { faSignOutAlt, faTimes, faShare, faCross, faUndo, faStickyNote } from "@fal"
import PureTextarea from "@/Components/Pure/PureTextarea.vue"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import { routeType } from "@/types/route"
import { notify } from "@kyvg/vue3-notification";

const layout = inject('layout', layoutStructure)

library.add(faTrashAlt, faSignOutAlt, faTimes, faShare, faCross, faUndo, faStickyNote, faPaperPlane)

const props = defineProps<{
    data: {}
    tab?: string
    state?: string
    route_checkmark : routeType
}>()

console.log(props)
const isPickingLoading = ref(false)
const isUndoLoading = ref(false)
const selectedRow = ref({})
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
        label: trans('Other incident'),
        value: 'other-incident'
    }
]
const selectedStatusNotPicked = reactive({
    status: 'other-incident',
    notes: ''
})
const errorNotPicked = reactive({
    status: null,
    notes: null
})
const isSubmitNotPickedLoading = ref<boolean | number>(false)

const onSubmitNotPicked = async (idPallet: number, closePopup: Function, routeNotPicked: routeType) => {
    isSubmitNotPickedLoading.value = idPallet
    router[routeNotPicked.method || 'get'](route(routeNotPicked.name, routeNotPicked.parameters), {
        state: selectedStatusNotPicked.status,
        notes: selectedStatusNotPicked.notes
    }, {
        onSuccess: () => {
            selectedStatusNotPicked.status = 'other'
            selectedStatusNotPicked.notes = ''
            errorNotPicked.status = null
            errorNotPicked.notes = null
            closePopup()
        },
        onError: (error: {}) => {
            console.error('hehehe', error)
        },
        onFinish: () => {
            isSubmitNotPickedLoading.value = false
        }
    })
}

const SetSelected = () => {
    const finalValue: Record<string, { quantity: number }> = [];

    for(const key in selectedRow.value){
        if (selectedRow.value[key]) {
           finalValue.push(key)
        }
    }

    router.post(
        route(props.route_checkmark.name, props.route_checkmark.parameters),
        { pallets : finalValue },
        {
            preserveScroll: true,
            onSuccess: () => {},
            onError: () => {
                notify({
                    title: 'Something went wrong.',
                    text: 'Failed to save',
                    type: 'error',
                });
            },
        }
    );

    console.log(finalValue)
};

const onChangeCheked = (value) => {
    selectedRow.value = value;
    SetSelected();
}


const setUpChecked = () => {
    const set: Record<string, boolean> = {};
    if (props.data?.data) {
        props.data.data.forEach((item) => {
            set[item.id] = item.is_checked || false;
        });
        selectedRow.value = set;
    }
};


onBeforeMount(() => {
    setUpChecked();
});


</script>

<template>
    <!-- <pre>{{data}}</pre> -->
    <Table :resource="data" :name="tab" class="mt-5" :isCheckBox="state == 'in-process'"
     @onSelectRow="onChangeCheked" :selectedRow="selectedRow"
    >

        <!-- Column: Type Icon -->
		<template #cell(type_icon)="{ item: palletDelivery }">

            <div class="space-x-1 space-y-1">
                <!-- Icon: Type -->
                <div v-if="layout.app.name == 'retina'" class="px-3">
                    <TagPallet :stateIcon="palletDelivery.type_icon" />
                </div>
                <FontAwesomeIcon v-else v-tooltip="palletDelivery.type_icon.tooltip" :icon='palletDelivery.type_icon.icon' :class='palletDelivery.type_icon.class' fixed-width aria-hidden='true' />
                <!-- Icon: State -->
                <div v-if="layout.app.name == 'retina'" class="px-3">
                    <TagPallet :stateIcon="palletDelivery.state_icon" />
                </div>
                            <Icon v-else :data="palletDelivery['state_icon']" class="px-1" />
            </div>

		</template>

        <!-- Column: Pallet Reference -->
		<template #cell(customer_reference)="{ item }">
			<div class="space-x-1 space-y-2">
				<span v-if="item.customer_reference" class="font-medium">{{ item.customer_reference }}</span>
				<span v-if="item.notes" class="text-gray-400 text-xs">
					<FontAwesomeIcon icon='fal fa-sticky-note' class='text-gray-400' fixed-width aria-hidden='true' />
					{{ item.notes }}
				</span>
                <span v-else class="text-gray-400 text-xs">-</span>
			</div>
		</template>

        <!-- Column: Rental -->
        <template #cell(rental)="{ item }">
                {{ item.rental_name }}
        </template>

        <!-- Column: State -->
		<!-- <template #cell(state)="{ item: palletDelivery }">
            <div v-if="layout.app.name == 'retina'" class="px-3">
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
                {{ trans('No items')}}
            </div>
        </template>


        <!-- Column: Location -->
		<template #cell(location)="{ item: palletDelivery }">
      <!--   <pre>{{ palletDelivery }}</pre> -->
            <Tag :label="palletDelivery.location_code" />
		</template>


        <!-- Column: Actions -->
        <template #cell(actions)="{ item: pallet }" v-if="props.state == 'in-process' || props.state == 'picking'">
            <!-- State: Pick or not-picked -->
            <div v-if="props.state == 'picking' && layout.app.name == 'Aiku'" class="flex gap-x-2 ">
                <!-- {{ pallet.state }} -->
                <!-- Button: Picking -->
                <Link v-if="pallet.state === 'picking'" as="div"
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
                    :href="route(pallet.undoPickingRoute.name, pallet.undoPickingRoute.parameters)"
                    :data="{ state: 'picked' }"
                    @start="() => isUndoLoading = pallet.id"
                    @finish="() => isUndoLoading = false"
                    method="patch"
                    v-tooltip="`Undo`"
                >
                    <Button icon="fal fa-undo" label="Undo picking" type="tertiary" size="xs" :loading="isUndoLoading === pallet.id" class="py-0" />
                </Link>

                <!-- Button: Set as not picked -->
                <Popover v-if="pallet.state === 'picking'">
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
                                <PureTextarea v-model="selectedStatusNotPicked.notes" @update:modelValue="() => errorNotPicked.notes = null" placeholder="Enter reason why the pallet is not picked" :class="errorNotPicked.notes ? 'errorShake' : ''" />
                                <div v-if="errorNotPicked.notes" class="mt-1 text-red-500 italic text-xxs">{{ errorNotPicked.notes }}</div>
                            </div>

                            <!-- Button: Save -->
                            <div class="flex justify-end mt-2">
                                <Button @click="async () => onSubmitNotPicked(pallet.id, close, pallet.notPickedRoute)"
                                    full
                                    label="Submit"
                                    :disabled="!selectedStatusNotPicked.status || !selectedStatusNotPicked.notes"
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
