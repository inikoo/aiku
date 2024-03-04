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
import SelectQuery from "@/Components/SelectQuery.vue"
import { useForm } from "@inertiajs/vue3"
import Modal from "@/Components/Utils/Modal.vue"
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"

library.add(faPlus)
const props = defineProps<{
    pallet: object
    storedItemsRoute: object
}>()


const emits = defineEmits()
const storedItem = useForm({ ...props.pallet })
const errors = ref({
    createStoredItem : null,
    storeStoredItem  : null
})
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


const SaveChange = async (option,select) => {
    storedItem.stored_items = option
    try {
        const response: any = await axios.post(route(props.pallet.storeStoredItemRoute.name,props.pallet.storeStoredItemRoute.parameters),
            {stored_item : storedItem.data().stored_items},
            { headers: {"Content-Type": "multipart/form-data"}}
        )
    return response.data
    errors.value.createStoredItem = null
    } catch (error: any) {
        errors.value.createStoredItem = null
        errors.value.storeStoredItem = error.response.data.message
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
    <div class="flex">
        <div class="max-w-80 min-w-64">
            <SelectQuery :route="route(storedItemsRoute.index.name, storedItemsRoute.index.parameters)" :value="storedItem"
                :placeholder="'Select Stored Items'" :required="true" :trackBy="'code'" :label="'reference'" :valueProp="'id'" :onChange="SaveChange"
                :closeOnSelect="true" :clearOnSearch="false" :fieldName="'stored_items'" mode="tags" :createOption="true" :onCreate="createPallet"/>
        </div>
      
       <!--  <div class="my-auto mx-auto p-1">
            <Button :icon="['fas', 'plus']" @click="() => (isModalOpen = true)" :type="'tertiary'" size="xs"></Button>
        </div>


        <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-fit">
            <Button class="sr-only" />
            <div class="space-y-4">
                <div class="flex justify-center gap-x-3">dsfsdf</div>
            </div>
        </Modal> -->
    </div>
    <p v-if="errors.createStoredItem" class="mt-2 text-xs text-red-600 max-w-80 min-w-64" id="email-error">{{ errors.createStoredItem }}</p>
    <p v-if="errors.storeStoredItem" class="mt-2 text-xs text-red-600 max-w-80 min-w-64" id="email-error">{{ errors.storeStoredItem }}</p>
</template>
