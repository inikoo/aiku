<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 14 Mar 2023 23:44:10 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import PureInput from "@/Components/Pure/PureInput.vue"
import PureInputNumber from "@/Components/Pure/PureInputNumber.vue"
import { faExclamationCircle, faCheckCircle, faTrash, faEdit } from "@fas"
import { faCopy } from "@fal"
import { faTrash as farTrash } from "@far"
import { faSpinnerThird } from "@fad"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref, inject } from "vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Popover from "@/Components/Popover.vue"
import { trans } from "laravel-vue-i18n"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import EmptyState from "@/Components/Utils/EmptyState.vue"


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
const currency = (inject('layout', layoutStructure))?.group?.currency


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
            let discountedPrice = item.price - (item.price * (bulkDiscInput.value / 100))
            item.agreed_price = discountedPrice
        }
    }

    bulkData.value = []
    close()
}

const showEdited = () => {
    const edited = []
    props.form[props.fieldName][props.bluprint.key].map((item, index) => {
        if (item.price != item.agreed_price) edited.push(item)
    })
    props.form[props.fieldName][props.bluprint.key] = edited
}

const showAll = () => {
    const edited = [...props.form[props.fieldName][props.bluprint.key]];
    // Iterate over each item in the source data array
    props.fieldData[props.bluprint.key].data.map((item) => {
        // Check if the current item is already present in the edited array
        const exists = edited.find((e) => e.slug == item.slug);

        // If the item is not present, add it to the edited array
        if (!exists) {
            edited.push(item);
        }
    });

    // Update the form field with the new edited array
    props.form[props.fieldName][props.bluprint.key] = edited;
};



</script>

<template>

    <div class="flex justify-between mb-3">
        <div>
            <Button
                v-if="props.fieldData[props.bluprint.key].data.length == props.form[props.fieldName][props.bluprint.key].length"
                :key="bluprint.key" :label="`Show Only Edited`" :type="'gray'" @click="showEdited" />
            <Button
                v-if="props.fieldData[props.bluprint.key].data.length != props.form[props.fieldName][props.bluprint.key].length"
                :key="bluprint.key" :label="`Show All`" :type="'gray'" @click="showAll" />
        </div>
        <Popover width="w-full" class="relative h-full">
            <template #button>
                <Button :key="bulkData.length" label="Set all discount (%)"
                    :type="bulkData.length > 0 ? 'edit' : 'disabled'" :icon="['fal', 'list-alt']" class="mr-2" />
            </template>

            <template #content="{ close: closed }">
                <div class="w-[200px]">
                    <div class="text-xs my-2 font-medium">{{ trans('Discount(%)') }}: </div>
                    <PureInputNumber v-model="bulkDiscInput" autofocus placeholder="1-100"  :maxValue="100"
                        :suffix="true" :minValue="0" @onEnter="() => onBulkDiscount(closed)">
                        <template #suffix>
                            <div
                                class="flex justify-center items-center px-2 absolute inset-y-0 right-0 gap-x-1 cursor-pointer hover:opacity-75 active:opacity-100 text-black">
                                %
                            </div>
                        </template>
                    </PureInputNumber>

                    <div class="flex justify-end mt-3">
                        <Button type="save" label="Set All" @click="() => onBulkDiscount(closed)" />
                    </div>
                </div>
            </template>
        </Popover>

    </div>



    <div class="-mx-4 -my-2 sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-visible shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300" :key="bluprint.key">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-4  pr-3 text-left text-sm font-semibold flex justify-center">
                                <input type="checkbox"
                                    class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600"
                                    :checked="(bulkData.length == form[fieldName][bluprint.key].length)"
                                    @change="onSelectAllRows" />
                            </th>
                            <th v-for="e in props.bluprint.column" cope="col"
                                class="px-3 py-3.5 text-left text-sm font-semibold">
                                {{ e.title }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="(itemData, index) in form[fieldName][bluprint.key]" :key="itemData.email">
                            <!-- Column: Selector -->
                            <td class="whitespace-nowrap px-3 py-4 text-sm  text-center w-20">
                                <input type="checkbox" :id="itemData.id" :value="itemData.id" v-model="bulkData"
                                    class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600" />
                            </td>
                            <td v-for="e in props.bluprint.column" :key="e.key"
                                :class="`whitespace-nowrap px-3 py-4 text-sm ${e.class}`">

                                <div v-if="!e.type || e.type == 'text'">
                                    {{ itemData[e.key] }}
                                </div>

                                <div v-if="e.type == 'price'">
                                    {{ `${currency?.symbol} ${itemData[e.key]}` }}
                                </div>

                                <div v-else-if="e.type == 'name'">
                                    <div>
                                        <div>{{ itemData["code"] }}</div>
                                        <div class="text-[10px]">{{ itemData["name"] }}</div>
                                    </div>
                                </div>

                                <div v-else-if="e.type == 'inputPrice'" class="w-28">
                                    <PureInputNumber v-model="itemData[e.key]" :placeholder="'Input price'" 
                                        :maxValue="itemData['price']" :prefix="true" :minValue="0" 
                                        @input="(value) => e?.propsOptions?.onChange(value, e, itemData)">
                                        <template #prefix>
                                            <div class="flex justify-center items-center pl-2">
                                                {{ `${currency?.symbol}` }}
                                            </div>
                                        </template>
                                    </PureInputNumber>
                                </div>

                                <div v-else-if="e.type == 'discount'" class="w-28">
                                    <PureInputNumber v-model="itemData[e.key]" :placeholder="'Input Discount'" 
                                        :suffix="true" :minValue="0"
                                        @input="(value) => e?.propsOptions?.onChange(value, e, itemData)">
                                        <template #suffix>
                                            <div
                                                class="flex justify-center items-center px-2 absolute inset-y-0 right-0 text-gray-400">
                                                %
                                            </div>
                                        </template>
                                    </PureInputNumber>
                                </div>

                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="form[fieldName][bluprint.key].length == 0">
                <EmptyState />
                </div>
            </div>
        </div>
    </div>
</template>
