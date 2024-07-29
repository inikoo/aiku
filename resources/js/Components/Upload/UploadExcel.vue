<script setup lang='ts'>

import ModalUpload from '@/Components/Utils/ModalUpload.vue'
import ProgressBar from '@/Components/Utils/ProgressBar.vue'
import { ref, computed, watch, onMounted} from 'vue';
import { router } from '@inertiajs/vue3'
import { useEchoGrpPersonal } from '@/Stores/echo-grp-personal'
import { cloneDeep } from 'lodash';
import { routeType } from '@/types/route'

interface UploadSpreadsheet {
    event: string
    channel: string
    required_fields: string[]
    template: {
        label: string
    }
    route: {
        upload: routeType
        history: routeType
        download: routeType
    }
}

const props = defineProps<{
    title: {
        label: string
        information: string
    }
    progressDescription: string
    upload_spreadsheet: UploadSpreadsheet
    scope?: string
    additionalDataToSend?: string[]
    
}>()


const model = defineModel()

const emits = defineEmits<{
    (e: 'onCloseModal', value: boolean): void
}>()

const echo = ref(cloneDeep(useEchoGrpPersonal()))

// console.log("uploadExcel",echo)

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
