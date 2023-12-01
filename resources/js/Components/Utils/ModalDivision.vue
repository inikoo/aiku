<script setup lang="ts">
import Modal from '@/Components/Utils/Modal.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { notify } from '@kyvg/vue3-notification'
import axios from 'axios'

const props = defineProps<{
    selectedWebsite: any
    selectedColumn: {
        label: string
        name: string
    }
    isModalOpen: boolean
    routeToSave?: {
        name: string
        parameter: string
    }
}>()

const emits = defineEmits(['onClose'])

// On select the button interested, not interested, not sure
const submitState = async (selectedColumnName: string, stateName: string) => {
    try {
        if(props.routeToSave) {
            // if routeToSave is provided
            await axios.post(
                route(props.routeToSave?.name, props.routeToSave?.parameter),
                {
                    division: selectedColumnName,
                    interest: stateName
                },
                {
                    headers: { "Content-Type": "multipart/form-data" },
                }
            )
            
            // To add Toast on success
            notify({
                title: 'Success!',
                text: 'Successfully changed the state',
                type: "success"
            })
        }
        
        // For manipulation data in client side (data change without refresh the page)
        props.selectedWebsite[props.selectedColumn.name].value = stateName

        // To close the modal
        emits('onClose')
    } catch (error: any) {
        notify({
            title: error.response.statusText,
            text: error.message,
            type: "error"
        })
    }
}
</script>

<template>
    <!-- Popup: for confirmation -->
    <Modal :isOpen="isModalOpen" @onClose="emits('onClose')" width="w-fit">
        <Button class="sr-only" /> <!-- Helper: to focused on popup Modal -->
        <div class="space-y-4">
            <p class="text-gray-600 text-center">Do you want to change the <span class="font-bold">{{ selectedColumn.label }}</span> status of <span class="font-bold">{{ selectedWebsite.name }}</span>?</p>
            <div class="flex justify-center gap-x-3">
                <Button v-if="selectedWebsite[selectedColumn.name].value != 'not_sure' && selectedWebsite[selectedColumn.name].value != null" @click="submitState(selectedColumn.name, 'not_sure')" :style="'tertiary'" label="Not sure" icon="far fa-circle" class="text-slate-500" />
                <Button v-if="selectedWebsite[selectedColumn.name].value != 'not_interested'" @click="submitState(selectedColumn.name, 'not_interested')" :style="'negative'" label="Not Interested" icon="fal fa-times-circle" />
                <Button v-if="selectedWebsite[selectedColumn.name].value != 'interested'" @click="submitState(selectedColumn.name, 'interested')" :style="'tertiary'" label="Interested" icon="fal fa-check-circle" class="border-green-500 text-green-500 focus:ring-green-500 hover:bg-green-50" />
            </div>
        </div>
    </Modal>
</template>