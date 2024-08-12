<script setup lang='ts'>

import ModalUpload from '@/Components/Utils/ModalUpload.vue'
import ProgressBar from '@/Components/Utils/ProgressBar.vue'

import { Upload } from '@/types/Upload'

const props = defineProps<{
    title: {
        label: string
        information: string
    }
    progressDescription: string
    upload_spreadsheet?: Upload
    scope?: string
    additionalDataToSend?: string[]
}>()


const model = defineModel()

const emits = defineEmits<{
    (e: 'onCloseModal', value: boolean): void
}>()

</script>

<template>
    <!-- Modal: Upload -->
    <KeepAlive>
        <ModalUpload
            v-model="model"
            :scope
            :title        
            :upload_spreadsheet
            :additionalDataToSend
        />
    </KeepAlive>

    <ProgressBar
        :description="progressDescription"
    />

</template>
