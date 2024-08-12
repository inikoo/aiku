<script setup lang="ts">
import Table from '@/Components/Table/Table.vue';
import Button from '@/Components/Elements/Buttons/Button.vue';
import Icon from "@/Components/Icon.vue";
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue';
import { ref, watch, onBeforeMount } from 'vue';
import { router } from "@inertiajs/vue3";
import { notify } from "@kyvg/vue3-notification";
import { useLayoutStore } from "@/Stores/layout";
import { debounce } from 'lodash';

const props = defineProps<{
    data?: { data: any[] };
    tab?: string;
    state: any;
    key: any;
    route_check_stored_items: routeType;
}>();

const isLoading = ref<string | boolean>(false);
const selectedRow = ref({});
const _table = ref(null);

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
    console.log(finalValue)

    router.post(
        route(props.route_check_stored_items.name, props.route_check_stored_items.parameters),
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
        
    </Table>
</template>
