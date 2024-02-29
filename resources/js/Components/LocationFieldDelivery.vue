<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core";
import { faTimesSquare } from "@fas";
import { faTrashAlt, faPaperPlane, faInventory } from "@far";
import { faSignOutAlt, faTruckLoading, faTimes } from "@fal";
import Flied from "@/Components/FieldEditableTable.vue";
import Button from "@/Components/Elements/Buttons/Button.vue";
import { ref, watch, defineEmits } from "vue";
import ButtonEditTable from "@/Components/ButtonEditTable.vue";
import Popover from '@/Components/Popover.vue'
import SelectQuery from '@/Components/SelectQuery.vue'
import { cloneDeep } from "lodash";
import { Link, router, useForm } from "@inertiajs/vue3";

library.add(
    faTrashAlt, faSignOutAlt, faPaperPlane, faInventory, faTruckLoading, faTimesSquare, faTimes
);
const props = defineProps<{
    pallet: object,
    locationRoute:object
}>();

const emits = defineEmits()
const location = useForm({ ...props.pallet })


const onSaveSuccess = (closed)=>{
    closed()
    emits('renderTableKey')
}

</script>
  
<template>
    <div class="relative">
        <Popover width="w-full">
            <template #button>
                <Button :type="pallet.state == 'booked-in' ? 'primary' : 'tertiary'" :icon="['fal', 'inventory']"
                    :tooltip="'Booked In'" :key="pallet.index" :size="'xs'" />
            </template>
            <template #content="{ close: closed }">
                <div class="w-[250px]">
                    <span class="text-xs px-1 my-2">Location : </span>
                    <div>
                        <SelectQuery :route="route(locationRoute.name, locationRoute.parameters)" :value="location"
                            :placeholder="'select location'" :required="true" :trackBy="'code'" :label="'code'"
                            :valueProp="'id'" :closeOnSelect="true" :clearOnSearch="false" :fieldName="'location_id'" />
                    </div>
                    <div class="flex justify-end mt-2">
                        <ButtonEditTable :type="'primary'" @onSuccess="onSaveSuccess(closed)"
                            :icon="['fas', 'save']" :tooltip="'Booked In'" :key="pallet.index" :size="'xs'"
                            :dataToSubmit="{location_id :location.data().location_id}" routeName="bookInRoute" :data="pallet"
                        />
                    </div>
                </div>
            </template>
        </Popover>
    </div>
</template>
  