<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"

import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import { Link,router} from "@inertiajs/vue3"
import Icon from "@/Components/Icon.vue"
import { faTimesSquare } from "@fas"
import { faTrashAlt, faPaperPlane, faInventory } from "@far"
import { faSignOutAlt, faTruckLoading } from "@fal"
import { useLayoutStore } from "@/Stores/retinaLayout"
import Flied from '@/Components/FieldEditableTable.vue'
import Button from "@/Components/Elements/Buttons/Button.vue";
import { method } from "lodash";
import { ref, watch, defineEmits } from "vue";
import ButtonEditTable from "@/Components/ButtonEditTable.vue" 

library.add(
    faTrashAlt, faSignOutAlt, faPaperPlane, faInventory, faTruckLoading, faTimesSquare
)
const props = defineProps<{
    data: object,
    tab?: string
    state?: string
    tableKey: number
}>()

const emits = defineEmits()

const loading = ref({
    loadingBookIn : false,
    loadingNotReceivedRoute : false
})

const onSave = async (pallet: object, fieldName: string) => {
    if (pallet[fieldName] != pallet.form.data()[fieldName]) {
        pallet.form.processing = true
        try {
            await axios.patch(
                route(pallet.updateRoute.name,
                    pallet.deleteRoute.parameters
                ),
                { [fieldName]: pallet.form.data()[fieldName] }
            )
            pallet.form.processing = false
            pallet.form.wasSuccessful = true
            pallet.form.hasErrors = false
            pallet.form.clearErrors()
        } catch (error: any) {
            pallet.form.processing = false
            pallet.form.wasSuccessful = false
            pallet.form.hasErrors = true
            if (error.response && error.response.data && error.response.data.errors) {
                const errors = error.response.data.errors
                const setErrors = {}
                for (const er in errors) {
                    setErrors[er] = errors[er][0]
                }
                pallet.form.setError(setErrors)
            } else {
                if (error.response.data.message)
                    notify({
                        title: "Failed to update",
                        text: error.response.data.message,
                        type: "error"
                    })
            }
        }

        // Setelah 5 detik, back to  normal
        setTimeout(() => {
            pallet.form.wasSuccessful = false
        }, 3000)
    }

}
const layout = useLayoutStore();

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5" :key="tableKey">
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
                <Link :href="route(pallet.deleteRoute.name, pallet.deleteRoute.parameters)" method="delete" as="button" :onSuccess="() => emits('renderTableKey')">
                <font-awesome-icon class="text-red-600" :icon="['far', 'trash-alt']" />
                </Link>
            </div>
            <div v-else-if="props.state == 'received' && !layout.currentRoute.includes('retina.')">

            <ButtonEditTable 
                class="mx-2"
                :type="pallet.state == 'not-received' ? 'negative' : 'tertiary'" 
                :icon="['fal', 'times']" 
                :tooltip="'Not Recived'" 
                :size="'xs'"
                :key="pallet.index"
                routeName="notReceivedRoute"
                :data="pallet"
                @onSuccess="() => emits('renderTableKey')"
            />

            <ButtonEditTable 
                :type="pallet.state == 'booked-in' ? 'primary' : 'tertiary'"  
                :icon="['fal', 'inventory']" 
                :tooltip="'Booked In'"
                :key="pallet.index"
                :size="'xs'" 
                routeName="bookInRoute"
                :data="pallet"
                @onSuccess="() => emits('renderTableKey')"
            />
            </div>
            </template>
        </Table>
</template>
