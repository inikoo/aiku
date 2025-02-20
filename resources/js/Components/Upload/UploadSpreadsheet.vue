<script setup lang='ts'>


import { Upload } from '@/types/Upload'
import ModalUploadSpreadsheet from '../Utils/ModalUploadSpreadsheet.vue';

const props = defineProps<{
    title: {
        label: string
        information: string
    }
    scope?: string
    additionalDataToSend?: string[]
    attachmentRoutes?: object
    options?: {
		name: string
		code: string
	}[]
}>()


const model = defineModel()

const emits = defineEmits<{
    (e: 'onCloseModal', value: boolean): void
}>()

</script>

<template>
    <KeepAlive>
        <ModalUploadSpreadsheet v-model="model" :scope :title :additionalDataToSend :attachmentRoutes :options  />
    </KeepAlive>

</template>
