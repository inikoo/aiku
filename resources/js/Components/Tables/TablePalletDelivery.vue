<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from '@/Components/Table/Table.vue';
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faTrashAlt } from '@far';
import { faSignOutAlt } from '@fal';
import PureInput from '@/Components/Pure/PureInput.vue';
import { useLayoutStore } from '@/Stores/layout';
import axios from 'axios';
import { notify } from '@kyvg/vue3-notification'

library.add(
    faTrashAlt, faSignOutAlt
)
const props = defineProps<{
    data: object,
    tab?: string
    state?: string
}>()


const onSave = async (id, value) => {
    const params = useLayoutStore().currentParams
    try {
        await axios.patch(
            route("grp.models.fulfilment-customer.pallet-delivery.pallet.update",
                {
                   ...params,
                    pallet: id
                }),
            value,
        )
    } catch (error: any) {
        console.log(error)
        if(error.response.data.message)
        notify({
            title: 'Failed to update',
            text: error.response.data.message,
            type: "error"
        })
    }
}

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(customer_reference)="{ item: item }">
            <div v-if="state == 'in-process'">
                <PureInput 
                    v-model="item.customer_reference"
                    @blur="(value) => onSave(item.id, { customer_reference: value })" 
                    @onEnter="(value) => onSave(item.id, { notes: value })"
                />
            </div>
            <div v-else>{{ item.customer_reference }}</div>
        </template>
        <template #cell(notes)="{ item: item }">
            <div v-if="state == 'in-process'">
                <PureInput 
                    v-model="item.notes" 
                    @blur="(value) => onSave(item.id, { notes: value })"  
                    @onEnter="(value) => onSave(item.id, { notes: value })"
                />
            </div>
            <div v-else>{{ item.notes }}</div>
        </template>
        <template #cell(actions)="{ item: pallet }">
            <font-awesome-icon class="text-red-600" :icon="['far', 'trash-alt']" />
        </template>
    </Table>
</template>
