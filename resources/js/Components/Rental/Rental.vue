<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 23:44:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import PureInput from "@/Components/Pure/PureInput.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faExclamationCircle, faCheckCircle, faTrash, faEdit } from '@fas'
import { faCopy } from '@fal'
import { faSpinnerThird } from '@fad'
import { library } from "@fortawesome/fontawesome-svg-core"
import { set, get } from "lodash"
import { ref, watch, defineEmits, onMounted } from "vue"
import SelectQuery from "@/Components/SelectQuery.vue"
import Button from "../Elements/Buttons/Button.vue"


library.add(faExclamationCircle, faCheckCircle, faSpinnerThird, faCopy, faTrash, faEdit)

const props = defineProps<{
    form: any
    fieldName: string
    options?: any
    fieldData?: {
        type: string
        placeholder: string
        readonly?: boolean
        copyButton: boolean
        maxLength?: number
    }
}>()

const emits = defineEmits()


const defaultValue = [
    { rental: null, price: 0, disc: 0, amount: 0 },
]


onMounted(() => {
    props.form[props.fieldName] = props.form[props.fieldName] ? props.form[props.fieldName] : defaultValue
})


const addRow = () => {
    props.form[props.fieldName].push({ rental: null, price: 0, disc: 0, amount: 0 })
}

const deleteRowRow = (index: number) => {
    props.form[props.fieldName].splice(index, 1)
}

</script>
<template>
    <div class="-mx-4 -my-2  sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-visible shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                Rental</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                Price</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                Discount (%)</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                Amount</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                <font-awesome-icon :icon="['fas', 'edit']" />
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="(itemData, index) in form[props.fieldName]" :key="itemData.email">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 w-80">
                                <div class="relative w-full">
                                    <SelectQuery
                                        :urlRoute="route(fieldData.indexRentalRoute.name, fieldData.indexRentalRoute.parameters)"
                                        :value="itemData" :placeholder="'Select or add rental'" :required="true"
                                        :label="'name'" :valueProp="'id'" :closeOnSelect="true" :clearOnSearch="false"
                                        :fieldName="'rental'" />
                                </div>

                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <PureInput v-model="itemData.price" :placeholder="'Input Price'" type="number"
                                    :minValue="0" step="0.01" />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <PureInput v-model="itemData.disc" :placeholder="'Input Price'" type="number"
                                    :suffix="true" :minValue="0" step="0.01">
                                    <template #suffix>
                                        <div
                                            class="flex justify-center items-center px-2 absolute inset-y-0 right-0 gap-x-1 cursor-pointer opacity-20 hover:opacity-75 active:opacity-100 text-black">
                                            %
                                        </div>
                                    </template>
                                </PureInput>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                {{ itemData.price  - (itemData.price * (itemData.disc / 100 )) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <font-awesome-icon :icon="['fas', 'trash']" class="text-red-500"
                                    @click="() => deleteRowRow(index)" />
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="pl-4 py-3 pb-4 text-left text-sm font-semibold text-gray-900">
                                <Button label="Add Row" type="create" @click="addRow" />
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</template>
