<script setup lang='ts'>


import { Upload } from '@/types/Upload'
import ModalUploadAttachment from '../Utils/ModalUploadAttachment.vue';

const props = defineProps<{
    title: {
        label: string
        information: string
    }
    scope?: string
    additionalDataToSend?: string[]
    attachmentRoutes?: object
}>()


const model = defineModel()

const emits = defineEmits<{
    (e: 'onCloseModal', value: boolean): void
}>()

</script>

<template>
    <KeepAlive>
        <ModalUploadAttachment v-model="model" :scope :title :additionalDataToSend :attachmentRoutes />
    </KeepAlive>

</template>
