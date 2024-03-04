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

console.log('asdasd',props.storedItemsRoute)

const emits = defineEmits()
const location = useForm({ ...props.pallet })
/* const isModalOpen = ref(false) */
console.log('sss',props.storedItemsRoute)

const createPallet = async (option, select) => {
    console.log(option)
    try {
        const response: any = await axios.post(route(props.storedItemsRoute.store.name,props.storedItemsRoute.store.parameters),
            {reference : option.id},
            { headers: {"Content-Type": "multipart/form-data"}}
        )
    } catch (error: any) {
        console.log(error)
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
        <div class="w-full">
            <SelectQuery :route="route(storedItemsRoute.index.name, storedItemsRoute.index.parameters)" :value="location"
                :placeholder="'Select Stored Items'" :required="true" :trackBy="'code'" :label="'name'" :valueProp="'id'"
                :closeOnSelect="true" :clearOnSearch="false" :fieldName="'location_id'" mode="tags" :createOption="true" :onCreate="createPallet"/>
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
</template>
