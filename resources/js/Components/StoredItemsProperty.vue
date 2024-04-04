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
import { useForm, router } from "@inertiajs/vue3"
import Modal from "@/Components/Utils/Modal.vue"
import { notify } from "@kyvg/vue3-notification"
import CreateStoredItems from "./CreateStoredItems.vue"
import Tag from '@/Components/Tag.vue'
import { get } from "lodash"

library.add(faPlus)

const props = defineProps<{
    pallet: {}
    storedItemsRoute: {}
    state?: string
}>()


const emits = defineEmits<{
    (e: 'renderTable'): void
}>()

const isModalOpen = ref(false)
const form = useForm({ id: null, quantity: 1, oldData: null })


const setFormOnEdit = (data) => {
    form.id = data.id
    form.quantity = data.quantity
    form.oldData = data
    isModalOpen.value = true
}


const setFormOnCreate = (data) => {
    form.reset()
    isModalOpen.value = true
}

/* const onSave = async () => {
    const stored_items = [...props.pallet.stored_items, {...form.data()}]
    const finalData = {}; // Change to object
    for(const d of stored_items) finalData[d.id] = {quantity : d.quantity}; // Assign each id as key
    sendToServer(finalData)
} */


const onDelete = (data) => {
    const stored_items = [...props.pallet.stored_items].filter((item) => item.id != data.id)
    const finalData = {} // Change to object
    for (const d of stored_items) finalData[d.id] = { quantity: d.quantity }
    sendToServer(finalData)
}


const sendToServer = async (data) => {
    router.post(route(props.pallet.storeStoredItemRoute.name, props.pallet.storeStoredItemRoute.parameters), { stored_item_ids: data }, {
        onError: (e) => {
            form.errors = {
                quantity: get(e, [`stored_item_ids.${form.data().id}.quantity`]),
                id: get(e, [`stored_item_ids`])
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
            form.errors = {}
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
            <div class="flex gap-x-1 gap-y-1.5 flex-wrap">
                <div v-for="item of pallet.stored_items" class="cursor-pointer">
                    <Tag @onClose="(event) => { event.stopPropagation(), onDelete(item) }" :theme="item.id"
                        :label="`${item.reference}`"
                        :closeButton="state == 'in-process' ? true : false" :stringToColor="true"
                        @click="() => state == 'in-process' ? setFormOnEdit(item) : null"
                    >
                        <template #label>
                            <div class="whitespace-nowrap text-xs">
                                {{ item.reference }} (<span class="font-light">{{ item.quantity }}</span>)
                            </div>
                        </template>
                    </Tag>

                </div>
                <Button v-if="state == 'in-process'" icon="fal fa-plus" @click="setFormOnCreate" :type="'dashed'" :size="'xs'"/>
            </div>
        </div>



        <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-1/2">
            <div class="sr-only" />
            <div class="space-y-4">
                <CreateStoredItems :storedItemsRoute="storedItemsRoute" :form="form" @onSave="sendToServer"
                    :stored_items="pallet.stored_items" />
            </div>
        </Modal>
    </div>
</template>
