<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core"
import { faPlus } from "@fas"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ref, defineEmits, onBeforeMount } from "vue"
import { useForm, router } from "@inertiajs/vue3"
import Modal from "@/Components/Utils/Modal.vue"
import { notify } from "@kyvg/vue3-notification"
import CreateStoredItems from "./CreateStoredItems.vue"
import Tag from '@/Components/Tag.vue'
import { fromPairs, get } from "lodash"

library.add(faPlus)
const props = defineProps<{
    pallet: object
    storedItemsRoute: object
}>()


const emits = defineEmits<{
    (e: 'renderTable'): void
}>()
const isModalOpen = ref(false)
const form = useForm({ id : null, quantity: 1, oldData : null })


const setFormOnEdit=(data)=>{
    form.id = data.id
    form.quantity = data.quantity
    form.oldData = data
    isModalOpen.value = true
}


const setFormOnCreate=(data)=>{
    form.reset()
    isModalOpen.value = true
}

/* const onSave = async () => {
    const stored_items = [...props.pallet.stored_items, {...form.data()}]
    const finalData = {}; // Change to object
    for(const d of stored_items) finalData[d.id] = {quantity : d.quantity}; // Assign each id as key
    sendToServer(finalData)
} */


const onDelete=(data)=>{
    const stored_items = [...props.pallet.stored_items].filter((item)=>item.id != data.id)
    const finalData = {}; // Change to object
    for(const d of stored_items) finalData[d.id] = {quantity : d.quantity};
    sendToServer(finalData)
}


const sendToServer=async(data)=>{
    router.post(route(props.pallet.storeStoredItemRoute.name, props.pallet.storeStoredItemRoute.parameters),  {stored_item_ids: data}, {
        onError: (e) => {
            form.errors = {
                quantity : get(e,[`stored_item_ids.${form.data().id}.quantity`]),
                id: get(e,[`stored_item_ids.${form.data().id}`])
            }
        notify({
            title: "Failed to add new stored items",
            text: "failed to update the stored items",
            type: "error"
        })
        },
        onSuccess: (e) => { 
            emits('renderTable')
            isModalOpen.value = false
            fromPairs.errors = {}
        },
        onBefore: () => {
            form.processing = true
        },
        onFinish: () => {
            form.processing = false
        }
    })
}

</script>

<template>
    <div class="flex">
        <div class="max-w-80 min-w-64">
            <div class="flex">
                <div v-for="item of pallet.stored_items">
                    <div class="w-fit p-[3px]">
                        <Tag  
                            @onClose="(event)=>{event.stopPropagation(),onDelete(item)}" 
                            :theme="item.id" :label="`${item.reference}(${item.quantity})`" 
                            :closeButton="true" :stringToColor="true" size="sm" 
                            @click="setFormOnEdit(item)"    
                        />
                    </div>

                </div>
                <div class="p-1">
                    <Button :icon="['fas', 'plus']" @click="setFormOnCreate" :type="'tertiary'"
                        size="xs"></Button>
                </div>
            </div>
        </div>



        <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-1/2">
            <Button class="sr-only" />
            <div class="space-y-4">
                <CreateStoredItems 
                    :storedItemsRoute="storedItemsRoute" 
                    :form="form"
                    @onSave="sendToServer"
                    :stored_items="pallet.stored_items"
                />
            </div>
        </Modal>
    </div>
</template>
