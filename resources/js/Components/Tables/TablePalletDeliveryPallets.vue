<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";

import PureInput from "@/Components/Pure/PureInput.vue";
import axios from "axios";
import { notify } from "@kyvg/vue3-notification";
import { Link } from "@inertiajs/vue3";
import Icon from "@/Components/Icon.vue";
import { faTimesSquare } from "@fas";
import { faTrashAlt, faPaperPlane, faInventory } from "@far";
import { faSignOutAlt, faTruckLoading } from "@fal";

library.add(
    faTrashAlt, faSignOutAlt, faPaperPlane, faInventory, faTruckLoading,faTimesSquare
);
const props = defineProps<{
    data: object,
    tab?: string
    state?: string
}>();


const onSave = async (pallet: object, value: object) => {
    try {
        await axios.patch(
            route(pallet.updateRoute.name,
                pallet.deleteRoute.parameters
            ),
            value
        );
    } catch (error: any) {
        console.log(error);
        if (error.response.data.message)
            notify({
                title: "Failed to update",
                text: error.response.data.message,
                type: "error"
            });
    }

};


</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(state)="{ item: palletDelivery }">
            <Icon :data="palletDelivery['state_icon']" class="px-1" />
        </template>
        <template #cell(customer_reference)="{ item: item }">
            <div v-if="state == 'in-process'">
                <PureInput
                    v-model="item.customer_reference"
                    @blur="(value) =>{ if(value) onSave(item, { customer_reference: value })}"
                    @onEnter="(value) =>{ if(value)  onSave(item, { customer_reference: value })}"
                />
            </div>
            <div v-else>{{ item["customer_reference"] }}</div>
        </template>
        <template #cell(notes)="{ item: item }">
            <div v-if="state == 'in-process'">
                <PureInput
                    v-model="item.notes"
                    @blur="(value) => { if(value) onSave(item, { notes: value }) } "
                    @onEnter="(value) => { if(value) onSave(item, { notes: value }) }"
                />
            </div>
            <div v-else>{{ item["notes"] }}</div>
        </template>
        <template #cell(actions)="{ item: pallet }">
            <div v-if="props.state == 'in-process'">
                <Link :href="route(pallet.deleteRoute.name,pallet.deleteRoute.parameters)" method="delete" as="button">
                    <font-awesome-icon class="text-red-600" :icon="['far', 'trash-alt']" />
                </Link>
            </div>
            <div v-else-if="props.state == 'received'">

                <Link :href="route(pallet.notReceivedRoute.name,pallet.notReceivedRoute.parameters)" method="patch" as="button">
                    <font-awesome-icon class="text-red-600 mr-6" :icon="['fas', 'times-square']" />
                </Link>

                <Link :href="route(pallet.bookinRoute.name,pallet.bookinRoute.parameters)" method="patch" as="button">
                    <font-awesome-icon :icon="['far', 'inventory']" />
                </Link>
            </div>

        </template>
    </Table>
</template>
