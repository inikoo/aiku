<script setup lang="ts">
import Table from '@/Components/Table/Table.vue';
import { ref, watch } from 'vue';
import Button from '@/Components/Elements/Buttons/Button.vue';
import Icon from "@/Components/Icon.vue";
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue';
import { Link, router } from "@inertiajs/vue3";
import { notify } from "@kyvg/vue3-notification";
import { Switch } from '@headlessui/vue';

const props = defineProps<{
    data?: {};
    tab?: string;
    state: any;
    key: any;
    route_check_stored_items : routeType;
}>();

console.log(props);

const isLoading = ref<string | boolean>(false);
const selectedRow = ref({});
const _table = ref(null);

const onShowSelected = () => {
    const data = props.data.data;
    const finalValue = {};

    for (const rowId in selectedRow.value) {
        if (selectedRow.value[rowId]) {
            // Find the corresponding data entry by id
            const tempData = data.find((item) => item.id == rowId);
            if (tempData) {
                // Add the selected item to the finalValue object
                finalValue[rowId] = { quantity: tempData.quantity };
            }
        }
    }

    router['post'](
        route(props.route_check_stored_items.name, props.route_check_stored_items.parameters),
        { stored_items: finalValue },
        {
            onSuccess: () => { },
            onError: (error: {} | string) => {
                notify({
                    title: 'Something went wrong.',
                    text: 'Failed to save',
                    type: 'error',
                });
            }
        });
};

watch(selectedRow, () => {
        onShowSelected();
});

</script>

<template>
    <Table :resource="data" :name="'stored_items'" class="mt-5" :isCheckBox="true"
        @onSelectRow="(value) => selectedRow = value" ref="_table">


        <template #cell(reference)="{ item: value }">
            {{ value.reference }}
        </template>

        <template #cell(state)="{ item: palletDelivery }">
            <Icon :data="palletDelivery['state_icon']" class="px-1" />
        </template>

        <template #cell(quantity)="{ item: item }">
            <div class='w-full flex justify-end'>
                <div class="flex w-32 justify-end">
                    <PureInputNumber v-model="item.data.quantity" @update:modelValue="(e) => item.quantity = e" :maxValue="item.total_quantity" :minValue="1" />
                </div>
            </div>
        </template>

        <!--  <template #cell(actions)="{ item: value }">
              <div v-if="state == 'in-process'">
                  <Link :href="route(value.deleteRoute.name, value.deleteRoute.parameters)" method="delete"
                      preserve-scroll as="div" @start="() => isLoading = 'delete' + value.id"
                      v-tooltip="'Delete Stored Item'">
                     <Button icon="far fa-trash-alt" :loading="isLoading === 'delete' + value.id" type="negative" />
                  </Link>
              </div>
          </template> -->

    </Table>
</template>