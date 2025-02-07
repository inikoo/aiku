<script setup lang="ts">
import Table from '@/Components/Table/Table.vue';
import Icon from "@/Components/Icon.vue";
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue';
import { ref, watch, onBeforeMount,reactive, inject} from 'vue';
import { notify } from "@kyvg/vue3-notification";
import { debounce, set } from 'lodash';
import { Link, router } from "@inertiajs/vue3"
import Popover from '@/Components/Popover.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { trans } from "laravel-vue-i18n"
import { routeType } from "@/types/route"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import PureTextarea from "@/Components/Pure/PureTextarea.vue"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import Tag from '@/Components/Tag.vue'
import NumberWithButtonSave from '@/Components/NumberWithButtonSave.vue'

const props = defineProps<{
    data?: { data: any[] };
    tab?: string;
    state: any;
    key: any;
    route_checkmark: routeType;
    palletReturn: {
        id: number
    }
}>();

const layout = inject('layout', layoutStructure)
const selectedRow = ref({});
const _table = ref(null);
const isPickingLoading = ref(false)
const isUndoLoading = ref(false)
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
        value: 'other_incident'
    }
]
const selectedStatusNotPicked = reactive({
    status: 'other_incident',
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

const setUpChecked = () => {
    const set: Record<string, boolean> = {};
    if (props.data?.data) {
        props.data.data.forEach((item) => {
            set[item.id] = item.is_checked || false;
        });
        selectedRow.value = set;
    }
};

const SetSelected = () => {
    const data = props.data?.data || [];
    const finalValue: Record<string, { quantity: number }> = {};

    for(const key in selectedRow.value){
        if (selectedRow.value[key]) {
            const tempData = data.find((item) => item.id == key);
            if (tempData) {
                finalValue[key] = { quantity: tempData.quantity };
            }
        }
    }

    router.post(
        route(props.route_checkmark.name, props.route_checkmark.parameters),
        { stored_items: finalValue },
        {
            preserveScroll: true,
            onSuccess: () => {},
            onError: (e) => {
                console.log('Failed to save', e);
                notify({
                    title: 'Something went wrong.',
                    text: 'Failed to save',
                    type: 'error',
                });
            },
        }
    );
};

const onChangeCheked = (value) => {
    selectedRow.value = value;
    SetSelected();
}

// Debounce the changeValueQty function
const changeValueQty = debounce(() => {
    SetSelected();
}, 1000);

/* watch(selectedRow, () => {
    SetSelected();
}, { deep: true }); */

onBeforeMount(() => {
    setUpChecked();
});

</script>

<template>
    <!-- {{ selectedRow }} -->
    <Table :resource="data" :name="'stored_items'" class="mt-5" :xxisCheckBox="state == 'in_process' ? true : false"
        @onSelectRow="onChangeCheked" ref="_table" :selectedRow="selectedRow">
        
        <!-- Column: Type icon -->
        <template #cell(type_icon)="{ item: value }">
            <Icon :data="value['type_icon']" class="px-1" />
        </template>
        
        <!-- Column: Reference -->
        <template #cell(reference)="{ item: value }">
            {{ value.reference }}
        </template>
        
        <!-- Column: Stored items -->
        <template #cell(pallet_stored_items)="{ item: value }">
            <div class="grid gap-y-1">
                <div v-for="pallet_stored_item in value.pallet_stored_items" :key="pallet_stored_item.id" class="rounded p-1 flex justify-between gap-x-4 items-center">
                    <!-- <Tag :label="pallet_stored_item.reference" stringToColor>
                        <template #label>
                            <div class="">
                                {{ pallet_stored_item.reference }} ({{ pallet_stored_item.quantity }})
                            </div>
                        </template>
                    </Tag> -->
                    <div>
                        {{ pallet_stored_item.reference }}
                        <span v-if="pallet_stored_item.location?.code" v-tooltip="trans('Location code of the pallet')" class="text-gray-400">({{ pallet_stored_item.location?.code }})</span>
                    </div>

                    <div class="flex items-center flex-nowrap gap-x-2">
                        <div v-tooltip="trans('Available quantity')" class="text-base">{{ pallet_stored_item.available_quantity }}</div>
                        <NumberWithButtonSave
                            v-model="pallet_stored_item.selected_quantity"
                            saveOnForm
                            :routeSubmit="{
                                name: pallet_stored_item.syncRoute.name,
                                parameters: {
                                    ...pallet_stored_item.syncRoute.parameters,
                                    palletReturn: palletReturn.id
                                },
                                method: pallet_stored_item.syncRoute.method
                            }"
                            keySubmit="quantity_ordered"
                            :bindToTarget="{
                                step: 1,
                                max: pallet_stored_item.max_quantity
                            }"
                        />
                    </div>
                    
                </div>
            </div>
        </template>

        <!-- Column: State -->
        <template #cell(state)="{ item: palletReturn }">
            <Icon :key="palletReturn['state_icon']?.icon" :data="palletReturn['state_icon']" class="px-1" />
        </template>

        <!-- Column: Quantity -->
        <template #cell(quantity)="{ item, proxyItem }">
            <div class="w-full flex justify-end">
                <div class="flex flex-col min-w-8 max-w-32">
                    <template v-if="state == 'in_process'">
                        <PureInputNumber
                            v-if="item.is_checked"
                            :modelValue="item.data.quantity"
                            :maxValue="item.total_quantity"
                            :minValue="1"
                            @update:modelValue="(e) => e ? (set(proxyItem, 'error_quantity', false), changeValueQty(e)) : set(proxyItem, 'error_quantity', true)"  
                        />
                        <PureInputNumber
                            v-else
                            :modelValue="0"
                            disabled
                            v-tooltip="trans('Check the row to edit')"
                        />

                        <p v-if="proxyItem.error_quantity" class="mt-1 text-left text-xs text-red-500 italic">*{{ trans('Quantity can\'t empty') }}</p>
                    </template>
                    
                    <div v-else class="py-3">{{ item.data.quantity }}</div>
                </div>
            </div>
        </template>

        <!-- Column: Actions -->
        <template #cell(actions)="{ item: pallet }" v-if="props.state == 'in_process' || props.state == 'picking'">
            <div v-if="props.state == 'picking' && layout.app.name == 'Aiku'" class="flex gap-x-2 relative">
                <Link v-if="pallet.state === 'picking'" as="div"
                    :href="route(pallet.updateRoute.name, pallet.updateRoute.parameters)"
                    :data="{ state: 'picked' }"
                    @start="() => isPickingLoading = pallet.id"
                    @finish="() => isPickingLoading = false"
                    method="patch"
                    v-tooltip="`Set as picked`"
                >
                    <Button icon="fal fa-check" type="positive" :loading="isPickingLoading === pallet.id" class="py-0" />
                </Link>

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

                <div class="relative">
                <Popover v-if="pallet.state === 'picking'" >
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
                    
                            <div class="mb-3">
                                <div class="text-xs px-1 mb-1"><span class="text-red-500 text-sm mr-0.5">*</span>Select status: </div>
                                <PureMultiselect v-model="selectedStatusNotPicked.status" @update:modelValue="() => errorNotPicked.status = null" :options="listStatusNotPicked" required caret :class="errorNotPicked.status ? 'errorShake' : ''" />
                                <div v-if="errorNotPicked.status" class="mt-1 text-red-500 italic text-xxs">{{ errorNotPicked.status }}</div>
                            </div>

                           
                            <div class="mb-4 ">
                                <div class="text-xs px-1 mb-1"><span class="text-red-500 text-sm mr-0.5">*</span>Description:</div>
                                <PureTextarea v-model="selectedStatusNotPicked.notes" @update:modelValue="() => errorNotPicked.notes = null" placeholder="Enter reason why the pallet is not picked" :class="errorNotPicked.notes ? 'errorShake' : ''" />
                                <div v-if="errorNotPicked.notes" class="mt-1 text-red-500 italic text-xxs">{{ errorNotPicked.notes }}</div>
                            </div>

                            
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
            </div>
        </template>

        
    </Table>
</template>
