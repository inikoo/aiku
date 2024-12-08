<script setup lang="ts">
import Button from "@/Components/Elements/Buttons/Button.vue"
import { ref } from "vue"
import { useForm, router } from "@inertiajs/vue3"
import Modal from "@/Components/Utils/Modal.vue"
import { notify } from "@kyvg/vue3-notification"
import CreateStoredItems from "./CreateStoredItems.vue"
import Tag from '@/Components/Tag.vue'
import { get } from "lodash"
import { routeType } from "@/types/route"


const props = defineProps<{
    pallet: {
        storeStoredItemRoute : routeType,
        stored_items :  Array<any>;
    }
    saveRoute : routeType
    storedItemsRoute: {
		store: routeType
		index: routeType
		delete: routeType
	}
    editable?: boolean
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

const setFormOnCreate = () => {
    form.reset()
    isModalOpen.value = true
}

const onDelete = (data : { id : ''}) => {
    const stored_items = [...props.pallet.stored_items].filter((item) => item.id != data.id)
    const finalData = {}
    for (const d of stored_items) finalData[d.id] = { quantity: d.quantity }
    sendToServer(finalData)
}

const sendToServer = async (data : {}) => {
    router.post(route(props.saveRoute.name, props.saveRoute.parameters), { stored_item_ids: data }, {
        onError: (e) => {
            form.errors = {
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
        <div class="flex gap-x-1.5 gap-y-1.5 flex-wrap">
            <template v-if="pallet?.stored_items?.length">
                <div v-for="item of pallet.stored_items" class="cursor-pointer">
                    <Tag @onClose="(event) => { event.stopPropagation(), onDelete(item) }" :theme="item.id"
                        :label="`${item.reference}`"
                        :closeButton="editable ? true : false" :stringToColor="true"
                        @click="() => editable ? setFormOnEdit(item) : null"
                    >
                        <template #label>
                            <div class="whitespace-nowrap text-xs">
                                {{ item.reference }} (<span class="font-light">{{ item.quantity }}</span>)
                            </div>
                        </template>
                    </Tag>
                </div>
            </template>
            
			<div v-else-if="!editable" class="pl-2.5 text-gray-400">
                -  
            </div>

            <Button v-if="editable" icon="fal fa-plus" @click="setFormOnCreate" :type="'dashed'" :size="'xs'"/>
        </div>

        <Modal :isOpen="isModalOpen" @onClose="isModalOpen = true" width="w-[600px]">
            <div class="space-y-4">
                <CreateStoredItems
                    :storedItemsRoute="storedItemsRoute"
                    :form="form"
                    @onSave="sendToServer"
                    :stored_items="pallet.stored_items"
                    @closeModal="isModalOpen = false"
                />
            </div>
        </Modal>
    </div>
</template>