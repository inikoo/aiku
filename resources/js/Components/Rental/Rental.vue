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
import { ref, watch, onMounted, onBeforeMount, isReadonly , inject}  from "vue"
import SelectQuery from "@/Components/SelectQuery.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"
import { v4 as uuidv4 } from 'uuid';
import Popover from '@/Components/Popover.vue'
import { trans } from "laravel-vue-i18n"
import Currency from "@/Components/Pure/Currency.vue"
import { layoutStructure } from '@/Composables/useLayoutStructure'


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

const rentals = ref([])
const bulkData = ref([])
const bulkDiscInput = ref(0)
const currency = inject('layout', layoutStructure).group.currency



const emits = defineEmits<{
    (e: 'changeIsDirty', value: Boolean): void
}>()

const defaultValue = [
    { id: uuidv4(), rental_id: null, price: 0, discount: 0, agreed_price: 0, original_price: 0 },
]

const addRow = () => {
    props.form[props.fieldName].push({ id: uuidv4(), rental_id: null, price: 0, discount: 0, agreed_price: 0, original_price: 0 })
}

const deleteRow = (index: number) => {
    props.form[props.fieldName].splice(index, 1)
    bulkData.value = []
}

const calculateDiscountedPrice = (price, discount) => {
    // Calculate the discounted price
    let discountedPrice = price - (price * (discount / 100));

    // Round the result to two decimal places
    discountedPrice = discountedPrice.toFixed(2);

    return discountedPrice;
};

const sePriceByRental = (value: number, options: Array, index: number) => {
    const data = options.find((item: { id: number }) => item.id == value)
    if (data) {
        console.log('rental_id',data)
        props.form[props.fieldName][index].price = data.price
        props.form[props.fieldName][index].original_price = data.price
        props.form[props.fieldName][index].agreed_price = calculateDiscountedPrice(props.form[props.fieldName][index].price, props.form[props.fieldName][index].discount)
    }
}


const sePriceByChange = (value: number, record: Object, index: number) => {
    props.form[props.fieldName][index].agreed_price = calculateDiscountedPrice(props.form[props.fieldName][index].price, props.form[props.fieldName][index].discount)
}




const getRentals = async () => {
    try {
        const response = await axios.get(route(props.fieldData.indexRentalRoute.name, props.fieldData.indexRentalRoute.parameters))
        rentals.value = response.data.data
    } catch (error) {
        console.log(error)
        notify({
            title: "Failed",
            text: "Error while fetching data",
            type: "error"
        })
    }
    props.form.isDirty = false
}


const onPutAllRentals = () => {
    const pullData = []
    const formData =  props.form[props.fieldName].filter((item)=> item.rental_id != null)
    for (const rental of rentals.value) {
        const find = formData.find((item) => item.rental_id == rental.id)
        if (!find) pullData.push({ id: uuidv4(), rental_id: rental.id, price: rental.price, discount: 0, agreed_price: rental.price, original_price: rental.price })
    }
    props.form[props.fieldName] = [...pullData]
}

/* const onChecked = (value) => {
    if (bulkData.value.length > props.form[props.fieldName].length)
        checkedAll.value = false
    if (bulkData.value.length == props.form[props.fieldName].length)
        checkedAll.value = true
    else checkedAll.value = false
} */

const selectAll = (input) => {
    const value = []
    if (input.target.checked) {
        props.form[props.fieldName].forEach((item) => value.push(item.id))
    }
    bulkData.value = value
}

const bulkDeleteAction = () => {
    const deleteData = [...props.form[props.fieldName]];
    const itemsToDelete = [];

    for (const [index, item] of deleteData.entries()) {
        if (bulkData.value.includes(item.id)) {
            itemsToDelete.push(index);
        }
    }

    // Remove items from deleteData in reverse order to avoid changing indices
    for (let i = itemsToDelete.length - 1; i >= 0; i--) {
        deleteData.splice(itemsToDelete[i], 1);
    }

    props.form[props.fieldName] = deleteData
    bulkData.value = []
};


const onBulkDiscAction = (close) => {
    const data = [...props.form[props.fieldName]];
    for (const [index, item] of data.entries()) {
        if (bulkData.value.includes(item.id)) {
            item.discount = bulkDiscInput.value
            item.agreed_price = calculateDiscountedPrice(item.price, item.discount)
        }
    }
    props.form[props.fieldName] = data
    bulkData.value = []
    close()
}

const setOptionSelectQueryFilter = (options, index) => {

    // Initialize an empty array to store filtered options 
    let pullData = [];

    // Find the first item in options whose id matches the rental property
    const first = options.find(item => item.id == props.form[props.fieldName][index]?.rental_id);

    // If such an item exists, add it to pullData
    if (first) {
        pullData.push({ ...first });
    }

    // Iterate through each option
    for (const rental of options) {
        // Check if the rental id exists in props.form[fieldName]
        const find = props.form[props.fieldName].find(item => item.rental_id === rental.id);
        // If not found, add the option to pullData
        if (!find) {
            pullData.push(rental);
        }
    }
    // Return the filtered options
    return pullData
};





onMounted(() => {
    getRentals()
    if (props.form[props.fieldName]) {
        const finalData = []
        const formData = [...props.form[props.fieldName]]
        for (const [index, item] of formData.entries()) {
            finalData.push({ ...item, id: uuidv4() })
        }
        props.form[props.fieldName] = finalData
        props.form.reset({ keepValues: true })
        props.form.reset()
    } else {
        props.form[props.fieldName] = []
    }
})



</script>
<template>
    <div class="flex justify-between py-4">
        <div>
            <Button label="Put all rental" type="create" @click="() => onPutAllRentals()" />
        </div>
        <div class="flex" v-if="(bulkData.length > 0)">
            <Popover width="w-full" class="relative h-full">
                <template #button>
                    <Button :key="bulkData.length" label="Set all discount (%)"
                        :type="bulkData.length > 0 ? 'edit' : 'disabled'" :icon='["far", "fa-pencil"]' class="mr-2" />
                </template>

                <template #content="{ close: closed }">
                    <div class="w-[350px]">
                        <div class="text-xs my-2 font-medium">{{ trans('Discount(%)') }}: </div>
                        <PureInput v-model="bulkDiscInput" autofocus placeholder="1-100" type="number" :maxValue="99"
                            :suffix="true" :minValue="0" @onEnter="() => onBulkDiscAction(closed)">
                            <template #suffix>
                                <div
                                    class="flex justify-center items-center px-2 absolute inset-y-0 right-0 gap-x-1 cursor-pointer hover:opacity-75 active:opacity-100 text-black">
                                    %
                                </div>
                            </template>
                        </PureInput>

                        <div class="flex justify-end mt-3">
                            <Button type="save" label="Set All" @click="() => onBulkDiscAction(closed)" />
                        </div>
                    </div>
                </template>
            </Popover>
            <Button :key="bulkData.length" label="Delete" :icon='["far", "fa-trash-alt"]'
                :type="bulkData.length > 0 ? 'delete' : 'disabled'" @click="bulkDeleteAction" />
        </div>
    </div>

    <div class="-mx-4 -my-2  sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-visible shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-3 py-4  pr-3 text-left text-sm font-semibold text-gray-900  flex justify-center">
                                <input type="checkbox"
                                    class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600"
                                    :checked="(bulkData.length === props.form[props.fieldName]?.length && props.form[props.fieldName]?.length > 0)"
                                    @change="selectAll" />
                            </th>
                            <th scope="col"
                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                {{ trans('Rental') }}</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                {{ trans('Price') }}</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                {{ trans('Discount (%)') }}</th>
                            <!-- <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                {{ trans('Agreed Price ($)') }}</th> -->
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                <font-awesome-icon :icon="['fas', 'edit']" />
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="(itemData, index) in form[props.fieldName]" :key="itemData.email">
                            <td class="whitespace-nowrap px-3 py-4 text-sm  text-center">
                                <input type="checkbox" :id="itemData.id" :value="itemData.id" v-model="bulkData"
                                    class="h-6 w-6 rounded cursor-pointer border-gray-300 hover:border-indigo-500 text-indigo-600 focus:ring-gray-600" />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm xl:w-[500px] w-full">
                                <div class="relative xl:w-[500px] w-full">
                                <div>
                                    <SelectQuery :filterOptions="(e) => setOptionSelectQueryFilter(e, index)"
                                        :key="itemData.id"
                                        :urlRoute="route(fieldData.indexRentalRoute.name, fieldData.indexRentalRoute.parameters)"
                                        :value="itemData" :placeholder="'Select or add rental'" :required="true"
                                        :label="'name'" :valueProp="'id'" :closeOnSelect="true" :clearOnSearch="false"
                                        :fieldName="'rental_id'"
                                        :on-change="(value, ref) => sePriceByRental(value, ref.options, index)" />
                                    </div>
                                    <p v-if="form.errors[`${fieldName}.${index}.rental`]"
                                        class="mt-2 text-sm text-red-600" :id="`${fieldName}-${index}-error`">
                                        {{ form.errors[`${fieldName}.${index}.rental`] }}
                                    </p>
                                </div>

                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm xl:w-[500px] w-full">
                                <Currency v-model="itemData.price" :placeholder="'Input Price'" :currency="currency.code"
                                    :minValue="0" step="0.01"
                                    @input="(value) => sePriceByChange(value, itemData, index)" />
                                <p v-if="form.errors[`${fieldName}.${index}.price`]" class="mt-2 text-sm text-red-600"
                                    :id="`${fieldName}-${index}-error`">
                                    {{ form.errors[`${fieldName}.${index}.price`] }}
                                </p>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm xl:w-[500px] w-full">
                                <PureInput v-model="itemData.discount" :placeholder="'Input Discount'" type="number"
                                    :maxValue="99" :suffix="true" :minValue="0"
                                    @input="(value) => sePriceByChange(value, itemData, index)">
                                    <template #suffix>
                                        <div
                                            class="flex justify-center items-center px-2 absolute inset-y-0 right-0 gap-x-1 cursor-pointer hover:opacity-75 active:opacity-100 text-black">
                                            %
                                        </div>
                                    </template>
                                </PureInput>
                            </td> 
                           <!--  <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <span class="text-sm font-semibold">$ {{ itemData.agreed_price }}</span>
                                <p v-if="form.errors[`${fieldName}.${index}.agreed_price`]"
                                    class="mt-2 text-sm text-red-600" :id="`${fieldName}-${index}-error`">      
                                    {{ form.errors[`${fieldName}.${index}.agreed_price`] }}
                                </p>
                            </td> -->
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <font-awesome-icon :icon="['fas', 'trash']" class="text-red-500"
                                    @click="() => deleteRow(index)" />
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="form[fieldName]?.length != rentals.length || props.form[props.fieldName]?.length == 0">
                        <tr>
                            <td colspan="4" class="pl-4 py-3 pb-4 text-left text-sm font-semibold text-gray-900">
                                <Button label="Rental Special Price" type="create" @click="addRow" />
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</template>
