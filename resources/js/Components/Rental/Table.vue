<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 23:44:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import PureInput from "@/Components/Pure/PureInput.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faExclamationCircle, faCheckCircle, faTrash, faEdit } from "@fas"
import { faCopy } from "@fal"
import { faTrash as farTrash } from "@far"
import { faSpinnerThird } from "@fad"
import { library } from "@fortawesome/fontawesome-svg-core"
import { set, get } from "lodash"
import { ref, watch, onMounted, onBeforeMount, isReadonly, inject } from "vue"
import SelectQuery from "@/Components/SelectQuery.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"
import { v4 as uuidv4 } from "uuid"
import Popover from "@/Components/Popover.vue"
import { trans } from "laravel-vue-i18n"
import Currency from "@/Components/Pure/Currency.vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"

library.add(faExclamationCircle, faCheckCircle, faSpinnerThird, faCopy, faTrash, farTrash, faEdit)

const props = defineProps<{
    form: any
    bluprint: Any
    fieldName: string
    options?: any
    fieldData?: {
        type: string
        placeholder: string
        readonly?: boolean
        copyButton: boolean
        maxLength?: number
        physical_goods: Object,
        rentals: Object,
        services: Object
    }
}>()

const bulkData = ref([])
const bulkDiscInput = ref(0)

const onSelectAllRows = (input) => {
    const value = []
    if (input.target.checked) {
        props.form[props.fieldName][props.bluprint.key].forEach((item) => value.push(item.id))
    }
    bulkData.value = value
}

const onBulkDiscount = (close: Function) => {
    for (const item of props.form[props.fieldName][props.bluprint.key]) {
        if (bulkData.value.includes(item.id)) {
            item.discount = bulkDiscInput.value       
        }
    }
    
    bulkData.value = []
    close()
}


</script>

<template>

    <div class="flex justify-end mt-3 mb-3">
        <Popover width="w-full" class="relative h-full">
            <template #button>
                <Button :key="bulkData.length" label="Set all discount (%)"
                    :type="bulkData.length > 0 ? 'edit' : 'disabled'" :icon='["far", "fa-pencil"]' class="mr-2" />
            </template>

            <template #content="{ close: closed }">
                <div class="w-[350px]">
                    <div class="text-xs my-2 font-medium">{{ trans('Discount(%)') }}: </div>
                    <PureInput v-model="bulkDiscInput" autofocus placeholder="1-100" type="number" :maxValue="99"
                        :suffix="true" :minValue="0" @onEnter="() => onBulkDiscount(closed)">
                        <template #suffix>
                            <div
                                class="flex justify-center items-center px-2 absolute inset-y-0 right-0 gap-x-1 cursor-pointer hover:opacity-75 active:opacity-100 text-black">
                                %
                            </div>
                        </template>
                    </PureInput>

                    <div class="flex justify-end mt-3">
                        <Button type="save" label="Set All" @click="() => onBulkDiscount(closed)" />
                    </div>
                </div>
            </template>
        </Popover>
       <!--  <Button :key="bulkData.length" :label="`Delete (${bulkData.length})`" :icon='["far", "fa-trash-alt"]'
            :type="bulkData.length > 0 ? 'delete' : 'disabled'" @click="bulkDeleteAction" /> -->
    </div>


    <div class="-mx-4 -my-2 sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-visible shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300" :key="bluprint.key">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-3 py-4  pr-3 text-left text-sm font-semibold  flex justify-center">
                                <input type="checkbox"
                                    class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600"
                                    :checked="(bulkData.length == form[fieldName][bluprint.key].length)"
                                    @change="onSelectAllRows" />
                            </th>
                            <th v-for="e in props.bluprint.column" cope="col"
                                class="px-3 py-3.5 text-left text-sm font-semibold min-w-40 max-w-80">
                                {{ e.title }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="(itemData, index) in form[fieldName][bluprint.key]" :key="itemData.email">
                            <!-- Column: Selector -->
                            <td class="whitespace-nowrap px-3 py-4 text-sm  text-center">
                                <input type="checkbox" :id="itemData.id" :value="itemData.id" v-model="bulkData"
                                    class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600" />
                            </td>
                            <td v-for="e in props.bluprint.column" :key="e.key"
                                class="whitespace-nowrap px-3 py-4 text-sm">
                                <div v-if="!e.type || e.type == 'text'">
                                    {{ itemData[e.key] }}
                                </div>
                                <div v-else-if="e.type == 'number'">
                                    <Currency v-model="itemData[e.key]" :placeholder="'Input Price'"
                                        :currency="itemData.currency.code" :minValue="0" step="0.01" />
                                </div>
                                <div v-else-if="e.type == 'discount'">
                                    <PureInput v-model="itemData[e.key]" :placeholder="'Input Discount'" type="number"
                                        :maxValue="99" :suffix="true" :minValue="0">
                                        <template #suffix>
                                            <div
                                                class="flex justify-center items-center px-2 absolute inset-y-0 right-0 text-gray-400">
                                                %
                                            </div>
                                        </template>
                                    </PureInput>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
