<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 20 Mar 2023 23:18:59 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">

import Button from "@/Components/Elements/Buttons/Button.vue"
import { ref, onMounted, onUnmounted } from "vue"
import { useForm, router } from "@inertiajs/vue3"
import Modal from "@/Components/Utils/Modal.vue"
import { notify } from "@kyvg/vue3-notification"
import CreateStoredItems from "./CreateStoredItems.vue"
import Tag from '@/Components/Tag.vue'
import { get } from "lodash"
import { trans } from "laravel-vue-i18n"
import SelectQuery from "@/Components/SelectQuery.vue"
import axios from 'axios'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBox, faHandHoldingBox, faPallet, faPencil, faPlus } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faBox, faHandHoldingBox, faPallet, faPencil, faPlus)

const props = defineProps<{
    pallet: {}
    storedItemsRoute: {}
    state?: string
}>()
console.log('sssdd', props)

const emits = defineEmits(['renderTable'])

const isModalOpen = ref(false)
const formModal = useForm({
    quantity: 1
})
const form = useForm({
    id: props.pallet.stored_items.map(item => item.id)
})

const createStoredItem = ref(false)

const setFormOnEdit = (data) => {
    formModal.quantity = data.quantity
    formModal.id = data.id
    isModalOpen.value = true
}



/* const setFormOnCreate = (data) => {
    form.reset()
    isModalOpen.value = true
} */

const createPallet = async (option, select) => {
    try {
        const response: any = await axios.post(
            route(props.storedItemsRoute.store.name, props.storedItemsRoute.store.parameters),
            { reference: option.id },
            { headers: { "Content-Type": "multipart/form-data" } }
        )
        form.errors = {}
        return {...response.data, total_quantity : 1}
    } catch (error: any) {
        form.errors.id = error.response.data.message
        notify({
            title: "Failed to add new stored items",
            text: error.response.data.message ? error.response.data.message : 'failed to create stored item',
            type: "error",
        })
        return false
    }
}

const onDelete = (data) => {
    const stored_items = [...props.pallet.stored_items].filter((item) => item.id != data.id)
    const finalData = {}
    for (const d of stored_items) finalData[d.id] = { quantity: d.quantity }
    sendToServer(finalData)
}

const sendToServer = async (data) => {
    router.post(route(props.pallet.storeStoredItemRoute.name, props.pallet.storeStoredItemRoute.parameters), { stored_item_ids: data }, {
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
            formModal.errors ={}
            formModal.reset()
        },
        onBefore: () => {
            form.processing = true
        },
        onFinish: () => {
            form.processing = false
        }
    })
}

const onCloseEdit = () => {
    createStoredItem.value = false;

    let newData = form.data().id;
    const finalData = {};

    // Match the IDs with the stored items and set the correct quantity
    newData.forEach(id => {
        const storedItem = props.pallet.stored_items.find(item => item.id === id);
        if (storedItem) {
            finalData[id] = { quantity: storedItem.quantity };
        } else {
            // If the ID is new, set default quantity to 1
            finalData[id] = { quantity: 1 };
        }
    });

    sendToServer(finalData);
}


const onChangeQuantity = () => {
    createStoredItem.value = false;

    let newData = props.pallet.stored_items
    const finalData = {};

    const index = newData.findIndex((item)=>item.id == formModal.id)
    if(index){
        newData[index].quantity = formModal.quantity
        newData.forEach(item => {
            finalData[item.id] = { quantity: item.quantity };
        })
        sendToServer(finalData);
    } else {
        notify({
                title: "Failed to edit stored items",
                text: 'cannot find stored item',
                type: "error"
            })
    }
    
}


onMounted(() => {
    if (typeof window !== 'undefined') {
        document.addEventListener('keydown', (e) => e.keyCode == 27 ? onCloseEdit() : '')
    }
})

onUnmounted(() => {
    document.removeEventListener('keydown', () => false)
})

</script>

<template>
    <div>
        <div class="min-w-[200px] relative p-0">
            <div v-if="!createStoredItem" class="flex gap-x-1 gap-y-1.5 mb-2">
                <template v-if="pallet.stored_items.length">
                    <div v-for="item in pallet.stored_items" :key="item.id" class="cursor-pointer">
                        <Tag @onClose="(event) => { event.stopPropagation(), onDelete(item) }" :theme="item.id"
                            :label="`${item.reference}`" :closeButton="state == 'in-process' ? true : false"
                            :stringToColor="true" @click="() => state == 'in-process' ? setFormOnEdit(item) : null">
                            <template #label>
                                <div class="whitespace-nowrap text-xs">
                                    {{ item.reference }} (<span class="font-light">{{ item.quantity }}</span>)
                                </div>
                            </template>
                        </Tag>
                    </div>
                </template>
                <div v-else class="italic text-gray-400">
                    Nothing
                </div>

                <div class="flex items-center px-1" @click="() => createStoredItem = true">
                    <FontAwesomeIcon :icon="faPlus" class='text-gray-400 text-lg cursor-pointer hover:text-gray-500'
                        fixed-width />
                </div>
            </div>

            <div v-else>
                <SelectQuery ref="_selectQuery" mode="tags"
                    :urlRoute="route(storedItemsRoute.index.name, storedItemsRoute.index.parameters)" :value="form"
                    :placeholder="'Select or add item'" :required="true" :trackBy="'reference'" :label="'reference'"
                    :valueProp="'id'" :closeOnSelect="true" :clearOnSearch="false" :fieldName="'id'"
                    :onCreate="createPallet" :createOption="true">
                    <template
                        #tag="{ option, handleTagRemove, disabled }: { option: tag, handleTagRemove: Function, disabled: boolean }">
                        <div class="px-0.5 py-[3px]">
                            <Tag :label="option.reference" :closeButton="true" :stringToColor="true" size="sm"
                                @onClose="(event) => handleTagRemove(option, event)" @click="(e) => {e.stopPropagation() ,state == 'in-process' ? setFormOnEdit(option) : null}" >
                                <template #label>
                                    <div class="whitespace-nowrap text-xs">
                                        {{ option.reference }} (<span class="font-light">{{ option.total_quantity == 0 ?  1 : option.quantity }}</span>)
                                    </div>
                                </template>    
                            </Tag>
                        </div>
                    </template>
                </SelectQuery>

                <div class="text-gray-400 italic text-xs">
                    Press Esc to finish edit or <span @click="onCloseEdit"
                        class="hover:text-gray-500 cursor-pointer">click
                        here</span>.
                </div>
                <p v-if="get(form, ['errors', 'id'])" class="mt-2 text-sm text-red-500">
			{{ form.errors.id }}
		</p>
            </div>
        </div>

        <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-[600px]">
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ trans("Quantity") }}</label>
                <div class="mt-1">
                    <input v-model="formModal.quantity" id="quantity" name="quantity" :autofocus="true" type="number"
                        autocomplete="quantity" :required="true" :min="1"
                        @update:modelValue="formModal.errors.quantity = ''"
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                </div>
                <div class="mt-2">
                    <Button full @click="onChangeQuantity" label="Submit" :loading="formModal.processing"> </Button>
                </div>
            </div>

        </Modal>
    </div>
</template>
