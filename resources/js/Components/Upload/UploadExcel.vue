<script setup lang='ts'>

import ModalUpload from '@/Components/Utils/ModalUpload.vue'
import ProgressBar from '@/Components/Utils/ProgressBar.vue'
import { ref, computed, watch, onMounted} from 'vue';
import { router } from '@inertiajs/vue3'
import { useEchoOrgPersonal } from '@/Stores/echo-org-personal'
import { cloneDeep } from 'lodash';

export interface routeType {
    name: string
    parameters?: string[]
    method?: string
}

const props = defineProps<{
    routes: {
        upload: routeType
        download?: routeType
        history?: routeType
    }
    dataModal: {
        isModalOpen: boolean
    }
    dataPusher: {
        channel: string
        event: string
    }
    description?: string
    propName?: string
}>()

const emits = defineEmits<{
    (e: 'onCloseModal', value: boolean): void
}>()

const echo = ref(cloneDeep(useEchoOrgPersonal()))



</script>

<template>
    <!-- Modal: Upload -->
    <KeepAlive>
        <ModalUpload
            v-model="dataModal.isModalOpen"
            :routes="routes"
            :propName="propName"
            :useEchoOrgPersonal="echo"
        />
    </KeepAlive>

    <ProgressBar
        :description="description"
        :echo="echo"
    />

</template>
