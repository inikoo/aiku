<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ref, defineEmits } from "vue"
import { useForm } from "@inertiajs/vue3"
import Modal from "@/Components/Utils/Modal.vue"
import { trans } from 'laravel-vue-i18n'
import SelectQuery from "@/Components/SelectQuery.vue"
import { notify } from "@kyvg/vue3-notification"
import axios from "axios"

library.add(faPlus)
const props = defineProps<{
    pallet: object
    storedItemsRoute: object
    form:object
    onSave:Function
}>()

const emits = defineEmits()


const createPallet = async (option, select) => {
    try {
        const response: any = await axios.post(route(props.storedItemsRoute.store.name,props.storedItemsRoute.store.parameters),
            {reference : option.id},
            { headers: {"Content-Type": "multipart/form-data"}}
        )
    errors.value.createStoredItem = null
    return response.data
    } catch (error: any) {
        errors.value.createStoredItem = error.response.data.message
        notify({
            title: "Failed to add new stored items",
            text: error,
            type: "error"
        })
        return false
    }
}




</script>
  
<template>
    <div>
        <label  class="block text-sm font-medium text-gray-700">{{ trans('Reference') }}</label>
        <div class="mt-1">
            <SelectQuery :route="route(storedItemsRoute.index.name, storedItemsRoute.index.parameters)" :value="form"  
                :placeholder="'Select Stored Items'" :required="true" :trackBy="'reference'" :label="'reference'" :valueProp="'id'"
                :closeOnSelect="true" :clearOnSearch="false" :fieldName="'id'" :createOption="true" :onCreate="createPallet"/>
        </div>
    </div>


    <div>
        <label class="block text-sm font-medium text-gray-700">{{ trans('Quantity') }}</label>
        <div class="mt-1">
            <input v-model="form.quantity"  id="quantity" name="quantity" :autofocus="true" type="number"
                autocomplete="quantity" :required="true"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
        </div>
    </div>

    <div class="space-y-2">
        <Button full @click="onSave"  label="Submit"> </Button>
    </div>
</template>
  