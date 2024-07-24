<script setup lang='ts'>

import ModalUpload from '@/Components/Utils/ModalUpload.vue'
import ProgressBar from '@/Components/Utils/ProgressBar.vue'
import { ref, computed, watch, onMounted} from 'vue';
import { router } from '@inertiajs/vue3'
import { useEchoGrpPersonal } from '@/Stores/echo-grp-personal'
import { cloneDeep } from 'lodash';
import { routeType } from '@/types/route'

const props = defineProps<{
    information?: string
    routes: {
        upload: routeType
        download?: routeType
        history?: routeType
    }
    required_fields?: string[]
    dataModal: {
        isModalOpen: boolean
    }
    description?: string
    propName?: string
}>()

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
            v-model="dataModal.isModalOpen"
            :routes="routes"
            :information="information"
            :propName="propName"
            :useEchoGrpPersonal="echo"
            :required_fields
        />
    </KeepAlive>

    <ProgressBar
        :description="description"
        :echo="echo"
    />

</template>
