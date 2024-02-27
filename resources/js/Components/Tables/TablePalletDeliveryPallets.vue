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
import {useLayoutStore} from "@/Stores/retinaLayout";
import Flied from '@/Components/FieldEditableTable.vue'

library.add(
    faTrashAlt, faSignOutAlt, faPaperPlane, faInventory, faTruckLoading,faTimesSquare
);
const props = defineProps<{
    data: object,
    tab?: string
    state?: string
}>();


const onSave = async (pallet: object, fieldName: string) => {
    console.log('inii',pallet,fieldName)
    pallet.form.processing = true;
    try {
        await axios.patch(
            route(pallet.updateRoute.name,
                pallet.deleteRoute.parameters
            ),
            { [fieldName]: pallet.form.data()[fieldName] }
        );
        pallet.form.processing = false;
        pallet.form.wasSuccessful = true;
        pallet.form.hasErrors = false;
    } catch (error: any) {
        pallet.form.processing = false;
        pallet.form.wasSuccessful = false;
        pallet.form.hasErrors = true;
        if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            const setErrors = {};
            for (const er in errors) {
                setErrors[er] = errors[er][0];
            }
            pallet.form.setError(setErrors);
        } else {
            if (error.response.data.message)
                notify({
                    title: "Failed to update",
                    text: error.response.data.message,
                    type: "error"
                });
        }
    }

    // Setelah 5 detik, back to  normal
    setTimeout(() => {
        pallet.form.wasSuccessful = false;
    }, 3000);
};

const layout = useLayoutStore();

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(state)="{ item: palletDelivery }">
            <Icon :data="palletDelivery['state_icon']" class="px-1" />
        </template>
        <template #cell(customer_reference)="{ item: item }">
            <div v-if="state == 'in-process'">
               <Flied :data="item" @onSave="onSave" fieldName="customer_reference" />
            </div>
            <div v-else>{{ item["customer_reference"] }}</div>
        </template>
        <template #cell(notes)="{ item: item }">
            <div v-if="state == 'in-process'">
                <Flied :data="item" @onSave="onSave" fieldName="note" />
            </div>
            <div v-else>{{ item["notes"] }}</div>
        </template>
        <template #cell(actions)="{ item: pallet }">
            <div v-if="props.state == 'in-process'">
                <Link :href="route(pallet.deleteRoute.name,pallet.deleteRoute.parameters)" method="delete" as="button">
                    <font-awesome-icon class="text-red-600" :icon="['far', 'trash-alt']" />
                </Link>
            </div>
            <div v-else-if="props.state == 'received' && !layout.currentRoute.includes('retina.')">

                <Link :href="route(pallet.notReceivedRoute.name,pallet.notReceivedRoute.parameters)" method="patch" as="button">
                    <font-awesome-icon class="text-red-600 mr-6" :icon="['fas', 'times-square']" />
                </Link>

                <Link :href="route(pallet.bookInRoute.name,pallet.bookInRoute.parameters)" method="patch" as="button">
                    <font-awesome-icon :icon="['far', 'inventory']" />
                </Link>
            </div>

        </template>
    </Table>
</template>
