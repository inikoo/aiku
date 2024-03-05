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
import axios from "axios"
import { notify } from "@kyvg/vue3-notification"
import CreateStoredItems from "./CreateStoredItems.vue"
import Tag from '@/Components/Tag.vue'

library.add(faPlus)
const props = defineProps<{
    pallet: object
    storedItemsRoute: object
}>()


const emits = defineEmits<{
    (e: 'renderTable'): void
}>()
const isModalOpen = ref(false)
const form = useForm({ id : null, quantity: 0 })


const setFormOnEdit=(data)=>{
    form.id = data.id
    form.quantity = data.quantity
    isModalOpen.value = true
}


const setFormOnCreate=(data)=>{
    form.reset()
    isModalOpen.value = true
}

const onSave = async () => {
    const stored_items = [...props.pallet.stored_items, {...form.data()}]
    const finalData = {}; // Change to object
    for(const d of stored_items) finalData[d.id] = {quantity : d.quantity}; // Assign each id as key
    sendToServer(finalData)
}


const onDelete=(data)=>{
    const stored_items = [...props.pallet.stored_items].filter((item)=>item.id != data.id)
    const finalData = {}; // Change to object
    for(const d of stored_items) finalData[d.id] = {quantity : d.quantity};
    sendToServer(finalData)
}


const sendToServer=async(data)=>{
    router.post(route(props.pallet.storeStoredItemRoute.name, props.pallet.storeStoredItemRoute.parameters),  {stored_item_ids: data}, {
        onError: (e) => {console.log('Error on confirm', e)},
        onSuccess: (e) => { emits('renderTable')},
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



        <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-fit">
            <Button class="sr-only" />
            <div class="space-y-4">
                <CreateStoredItems 
                :pallet="pallet" 
                :storedItemsRoute="storedItemsRoute" 
                :form="form"
                :onSave="onSave"
                />
            </div>
        </Modal>
    </div>
</template>
