<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Table from "@/Components/Table/Table.vue";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";

import axios from "axios";
import { notify } from "@kyvg/vue3-notification";
import { Link, router, useForm } from "@inertiajs/vue3";
import Icon from "@/Components/Icon.vue";
import { faTimesSquare } from "@fas";
import { faTrashAlt, faPaperPlane, faInventory } from "@far";
import { faSignOutAlt, faTruckLoading, faTimes } from "@fal";
import { useLayoutStore } from "@/Stores/retinaLayout";
import Flied from "@/Components/FieldEditableTable.vue";
import Button from "@/Components/Elements/Buttons/Button.vue";
import { ref, watch, defineEmits } from "vue";
import ButtonEditTable from "@/Components/ButtonEditTable.vue";
import Popover from '@/Components/Popover.vue'
import SelectQuery from '@/Components/SelectQuery.vue'

library.add(
    faTrashAlt, faSignOutAlt, faPaperPlane, faInventory, faTruckLoading, faTimesSquare, faTimes
);
const props = defineProps<{
    data: object,
    tab?: string
    state?: string
    tableKey: number
    locationRoute:{}
}>();

const emits = defineEmits();
const location = useForm({ location_id : null })


const onSaved = async (pallet: object, fieldName: string) => {
    if (pallet[fieldName] != pallet.form.data()[fieldName]) {
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
            pallet.form.clearErrors();
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
    }

};

</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5" :key="tableKey">
        <template #cell(state)="{ item: palletDelivery }">
            <Icon :data="palletDelivery['state_icon']" class="px-1" />
        </template>
        <template #cell(customer_reference)="{ item: item }">
            <div v-if="state == 'in-process'">
                <Flied :data="item" @onSave="onSaved" fieldName="customer_reference" />
            </div>
            <div v-else>{{ item["customer_reference"] }}</div>
        </template>
        <template #cell(notes)="{ item: item }">
            <div v-if="state == 'in-process'">
                <Flied :data="item" @onSave="onSaved" fieldName="notes" />
            </div>
            <div v-else>{{ item["notes"] }}</div>
        </template>
        <template #cell(actions)="{ item: pallet }">
            <div v-if="props.state == 'in-process'">
                <Link :href="route(pallet.deleteRoute.name, pallet.deleteRoute.parameters)" method="delete" as="button"
                    :onSuccess="() => emits('renderTableKey')">
                <font-awesome-icon class="text-red-600" :icon="['far', 'trash-alt']" />
                </Link>
            </div>
            <div v-if="pallet.state == 'not-received'">
                <ButtonEditTable class="mx-2" :type="'red'" :icon="['fas', 'trash-undo-alt']" :tooltip="'Undo Pallet'"
                    :size="'xs'" :key="pallet.index" routeName="undoNotReceivedRoute" :data="pallet"
                    @onSuccess="() => emits('renderTableKey')" />
            </div>
            <div v-else-if="pallet.state == 'received'">
                <!--     <pre>{{ pallet }}</pre> -->
                <div class="flex">
                    <ButtonEditTable class="mx-2" :type="pallet.state == 'not-received' ? 'negative' : 'tertiary'"
                        :icon="['fal', 'times']" :tooltip="'Not Recived'" :size="'xs'" :key="pallet.index"
                        routeName="notReceivedRoute" :data="pallet" @onSuccess="() => emits('renderTableKey')" />

                    <!-- <ButtonEditTable
                        :type="pallet.state == 'booked-in' ? 'primary' : 'tertiary'"
                        :icon="['fal', 'inventory']"
                        :tooltip="'Booked In'"
                        :key="pallet.index"
                        :size="'xs'"
                        routeName="bookInRoute"
                        :data="pallet"
                        @onSuccess="() => emits('renderTableKey')"
                        /> -->


                    <div class="relative">
                        <Popover width="w-full">
                            <template #button>
                                <Button :type="pallet.state == 'booked-in' ? 'primary' : 'tertiary'"
                                    :icon="['fal', 'inventory']" :tooltip="'Booked In'" :key="pallet.index" :size="'xs'" />
                            </template>
                            <template #content="{ close: closed }">
                                <div class="w-[250px]">
                                    <span class="text-xs px-1 my-2">Location : </span>
                                    <div>
                                      <SelectQuery
                                        :route="route(locationRoute.name,locationRoute.parameters)"
                                        :value="location.location_id"
                                        :placeholder="'select location'"
                                        :required="true"
                                        :trackBy="'code'"
                                        :label="'code'"
                                        :valueProp="'id'"
                                        :closeOnSelect="true"
                                        :clearOnSearch="false"
                                     />
                                    </div>
                                    <div>
                                        <ButtonEditTable
                                        :type="pallet.state == 'booked-in' ? 'primary' : 'tertiary'"
                                        :icon="['fal', 'inventory']"
                                        :tooltip="'Booked In'"
                                        :key="pallet.index"
                                        :size="'xs'"
                                        :dataToSubmit="location.data()"
                                        routeName="bookInRoute"
                                        :data="pallet"
                                        @onSuccess="() => emits('renderTableKey')"
                                        />
                                    </div>
                                </div>
                            </template>
                        </Popover>
                    </div>
                </div>

            </div>
        </template>
    </Table>
</template>
