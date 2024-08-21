<script setup lang="ts">
import Table from '@/Components/Table/Table.vue';
import Icon from "@/Components/Icon.vue";
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue';
import { ref, watch, onBeforeMount,reactive, inject} from 'vue';
import { notify } from "@kyvg/vue3-notification";
import { debounce } from 'lodash';
import { Link, router } from "@inertiajs/vue3"
import Popover from '@/Components/Popover.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { trans } from "laravel-vue-i18n"
import { routeType } from "@/types/route"
import { layoutStructure } from "@/Composables/useLayoutStructure"

const props = defineProps<{
    data?: { data: any[] };
    tab?: string;
    state: any;
    key: any;
    route_checkmark: routeType;
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
            onError: () => {
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
}, 500);

/* watch(selectedRow, () => {
    SetSelected();
}, { deep: true }); */

onBeforeMount(() => {
    setUpChecked();
});

</script>

<template>
    <Table :resource="data" :name="'stored_items'" class="mt-5" :isCheckBox="state == 'in-process' ? true : false"
        @onSelectRow="onChangeCheked" ref="_table" :selectedRow="selectedRow">
        
        <template #cell(reference)="{ item: value }">
            {{ value.reference }}
        </template>

        <template #cell(state)="{ item: palletDelivery }">
            <Icon :data="palletDelivery['state_icon']" class="px-1" />
        </template>

        <template #cell(quantity)="{ item: item }">
            <div class="w-full flex justify-end">
                <div class="flex min-w-8 max-w-32 justify-end">
                    <PureInputNumber v-if="item.is_checked && state == 'in-process'" v-model="item.data.quantity"
                        :maxValue="item.total_quantity" :minValue="1" @update:modelValue="changeValueQty" />
                    <div v-if="state != 'in-process'" class="py-3">{{ item.data.quantity }}</div>
                </div>
            </div>
        </template>

        <template #cell(actions)="{ item: pallet }" v-if="props.state == 'in-process' || props.state == 'picking'">
            <div v-if="props.state == 'picking' && layout.app.name == 'Aiku'" class="flex gap-x-2 ">
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
        </template>

        
    </Table>
</template>
