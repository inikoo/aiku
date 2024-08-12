<script setup lang="ts">
import Table from '@/Components/Table/Table.vue';
import Button from '@/Components/Elements/Buttons/Button.vue';
import Icon from "@/Components/Icon.vue";
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue';
import { ref, watch, onBeforeMount } from 'vue';
import { router } from "@inertiajs/vue3";
import { notify } from "@kyvg/vue3-notification";
import { useLayoutStore } from "@/Stores/layout";

type routeType = {
  name: string;
  parameters: Record<string, any>;
};

const props = defineProps<{
    data?: { data: any[] };
    tab?: string;
    state: any;
    key: any;
    route_check_stored_items: routeType;
}>();

const isLoading = ref<string | boolean>(false);
const selectedRow = ref<Record<string, boolean>>({});
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

    Object.keys(selectedRow.value).forEach((rowId) => {
        if (selectedRow.value[rowId]) {
            const tempData = data.find((item) => item.id === rowId);
            if (tempData) {
                finalValue[rowId] = { quantity: tempData.quantity };
            }
        }
    });

    router.post(
        route(props.route_check_stored_items.name, props.route_check_stored_items.parameters),
        { stored_items: finalValue },
        {
            onSuccess: () => {
                notify({
                    title: 'Success',
                    text: 'Items successfully saved.',
                    type: 'success',
                });
            },
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

watch(selectedRow, () => {
    SetSelected();
}, { deep: true });

onBeforeMount(() => {
    setUpChecked();
});

</script>

<template>
    <Table :resource="data" :name="'stored_items'" class="mt-5" :isCheckBox="true"
        @onSelectRow="(value) => selectedRow.value = value" ref="_table" :selectedRow="selectedRow">
        
        <template #cell(reference)="{ item: value }">
            {{ value.reference }}
        </template>

        <template #cell(state)="{ item: palletDelivery }">
            <Icon :data="palletDelivery['state_icon']" class="px-1" />
        </template>

        <template #cell(quantity)="{ item: item }">
            <div class="w-full flex justify-end">
                <div class="flex w-32 justify-end">
                    <PureInputNumber v-model="item.quantity" @update:modelValue="(e) => item.quantity = e" 
                        :maxValue="item.total_quantity" :minValue="1" />
                </div>
            </div>
        </template>
        
    </Table>
</template>
