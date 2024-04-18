<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ref, defineEmits } from "vue"
import ButtonEditTable from "@/Components/ButtonEditTable.vue"
import Popover from '@/Components/Popover.vue'
import SelectQuery from '@/Components/SelectQuery.vue'
import { useForm } from "@inertiajs/vue3"
import { routeType } from "@/types/route"
import { Pallet } from "@/types/Pallet"

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faTimesSquare } from "@fas"
import { faTrashAlt, faPaperPlane, faInventory } from "@far"
import { faSignOutAlt, faTruckLoading, faPencil, faTimes } from "@fal"
library.add( faTrashAlt, faSignOutAlt, faPaperPlane, faInventory, faTruckLoading, faPencil, faTimesSquare, faTimes )

const props = defineProps<{
    pallet: Pallet
    locationRoute?: routeType
}>()

const emits = defineEmits<{
    (e: 'renderTableKey'): void
}>()
const location = useForm({ ...props.pallet })
const error = ref({})

const isPalletDamaged = ref(props.pallet.state === 'damaged')

const onSaveSuccess = (closed: Function) => {
    closed()
    emits('renderTableKey')
    error.value = {}
}


const onSaveError = (errorValue: any) => {
    error.value = errorValue
}

</script>

<template>
    <div class="relative">
    <!-- <pre>{{ pallet.state }}</pre> -->
        <Popover width="w-full">
            <template #button>
                <!-- <Button :type="pallet.state == 'booked-in' ? 'primary' : 'tertiary'" :icon="['fal', 'inventory']"
                    tooltip="Set location for pallet" :key="pallet.index" :size="'xs'" /> -->
                <div v-if="pallet.location" class="text-gray-400">
                    {{ pallet.location }}
                    <FontAwesomeIcon icon='fal fa-pencil' size="sm" class='ml-1' fixed-width aria-hidden='true' />
                </div>

                <Button v-else-if="pallet.state !== 'not-received'"
                    type="primary"
                    label="Set location"
                    tooltip="Set location for pallet"
                    :key="pallet.index"
                    :size="'xs'" />
            </template>

            <template #content="{ close: closed }">
                <div class="w-[250px]">
                    <span class="text-xs px-1 my-2">Location: </span>
                    <div>
                        <SelectQuery
                            :urlRoute="route(locationRoute?.name, locationRoute?.parameters)"
                            :value="location"
                            :placeholder="'Select location'"
                            :required="true"
                            :trackBy="'code'"
                            :label="'code'"
                            :valueProp="'id'"
                            :closeOnSelect="true"
                            :clearOnSearch="false"
                            :fieldName="'location_id'"
                            @updateVModel="() => error.location_id = ''"
                        />
                        
                        <div class="flex gap-x-1 items-center mt-2 pl-0.5">
                            <input v-model="isPalletDamaged" type="checkbox" id="checkboxLocation" class="rounded border-gray-300 text-red-500 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="checkboxLocation" class="select-none cursor-pointer text-gray-500">Set as damaged</label>
                        </div>

                        <p v-if="error.location_id" class="mt-2 text-sm text-red-600">{{ error.location_id }}</p>
                    </div>

                    <!-- Button: Save -->
                    <div class="flex justify-end mt-2">
                        <ButtonEditTable
                            type="primary"
                            @onSuccess="onSaveSuccess(closed)"
                            :icon="['fas', 'save']"
                            tooltip="Save location"
                            :key="pallet.index"
                            :size="'xs'"
                            :disabled="!location.location_id"
                            @onError="onSaveError"
                            :dataToSubmit="{ location_id: location.data().location_id, damaged: isPalletDamaged}"
                            routeName="bookInRoute"
                            :data="pallet" />
                    </div>
                </div>
            </template>
        </Popover>
    </div>
</template>