<script setup lang="ts">
import { ref, watch } from 'vue'
import { trans } from 'laravel-vue-i18n'

import Modal from '@/Components/Utils/Modal.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faFile as falFile, faTimes } from '@fal'
import { faFileDownload, faDownload } from '@fas'
import { faInfoCircle } from '@fad'
import { library } from '@fortawesome/fontawesome-svg-core'
import axios from 'axios'
import { useFormatTime } from '@/Composables/useFormatTime'
import { routeType } from '@/types/route'
import { Link } from "@inertiajs/vue3"
import { useEchoGrpPersonal as echo } from '@/Stores/echo-grp-personal'
import PureInput from '../Pure/PureInput.vue'

library.add(falFile, faTimes, faFileDownload, faDownload, faInfoCircle)

const props = defineProps<{
    modelValue: boolean
    routes: {
        upload: routeType
        download?: routeType
        history?: routeType
    }
    information?: string
    propName?: string
    useEchoGrpPersonal: {
        isShowProgress: boolean
    }
    // isUploaded: boolean
}>()


const emits = defineEmits();

// const { isUploaded } = toRefs(props)

const isLoadingUpload = ref(false)
const dataHistoryFileUpload: any = ref([])
const isLoadingHistory = ref(false)
const isDraggedFile = ref(false)
const errorMessage = ref(false)
const isIncludeStoreItems = ref(false)

// Running when file is uploaded or dropped
const onUploadFile = async (fileUploaded: File) => {
    isDraggedFile.value = false
    errorMessage.value = false
    isLoadingUpload.value = true
    try {
        await axios.post(
            route(props.routes.upload.name, props.routes.upload.parameters),
            {
                file: fileUploaded,
            },
            {
                headers: { "Content-Type": "multipart/form-data" },
            }
        )
        props.useEchoGrpPersonal.isShowProgress = true

    } catch (error: any) {
        // console.log(error.response.data.message)
        errorMessage.value = error?.response?.data?.message
    }
    isLoadingUpload.value = false
}

const closeModal = () =>{
 /*    useEchoGrpPersonal().isShowProgress = false */
    props.useEchoGrpPersonal.isShowProgress = false
    emits('update:modelValue', false)
}

// Fetch data history when Modal is opened
watch(() => props.modelValue, async (newVal) => {
    if (props.routes.history?.name) {
        isLoadingHistory.value = true
        if(newVal && !dataHistoryFileUpload.value.length) {  // to prevent fetch every modal appear
                console.log(props.routes)
            try {
                const data = await axios.get(route(props.routes.history.name, props.routes.history.parameters))
                dataHistoryFileUpload.value = data.data.data
            } catch (error: any) {
                dataHistoryFileUpload.value = []
                console.error(error.message)
            }
        }
    } else {
        dataHistoryFileUpload.value = []
    }
    isLoadingHistory.value = false
})

</script>

<template>
    <Modal :isOpen="modelValue" @onClose="() => closeModal()" :closeButton="true">
        <!-- <div @click="emits('update:modelValue', false)" class="group px-2 absolute right-6 top-4 cursor-pointer">
            <FontAwesomeIcon icon='fal fa-times' class='text-gray-400 group-hover:text-gray-600' aria-hidden='true' />
        </div> -->

        <!-- Title -->
        <div class="flex justify-center py-2 text-gray-600 font-medium mb-3">
            <div>
                <div class="flex gap-x-0.5">
                    {{ trans(`Upload your new ${propName}`) }}
                    <VTooltip v-if="information" class="w-fit">
                        <FontAwesomeIcon icon='fad fa-info-circle' size="xs" class='text-gray-500' fixed-width aria-hidden='true' />

                        <template #popper>
                            <div class="min-w-20 w-fit max-w-52 text-xs">
                                {{ information }}
                            </div>
                        </template>
                    </VTooltip>
                </div>
                
                <div class="flex justify-center">
                    <a v-if="routes?.download?.name" :href="route(routes?.download?.name, routes?.download?.parameters)" class="group text-xs text-gray-600 cursor-pointer px-2 w-fit" download>
                        <span class="text-xs text-gray-400 group-hover:text-gray-600">
                            <FontAwesomeIcon icon='fas fa-file-download' class='text-gray-400 group-hover:text-gray-600' aria-hidden='true' />
                            {{ trans(`Download template .xlsx`) }}
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-x-3">
            <!-- Column upload -->
            <div
                @drop="(e: any) => (e.preventDefault(), onUploadFile(e.dataTransfer.files[0]))"
                @dragover.prevent
                @dragenter.prevent
                @dragleave.prevent
                class="relative flex items-center justify-center rounded-lg border border-dashed border-gray-700/25 px-6 h-48 bg-gray-400/10"
                :class="{'hover:bg-gray-400/20': !isLoadingUpload}"
            >
                <!-- Section: Upload area -->
                <div v-if="!isLoadingUpload">
                    <label for="fileInput"
                        class="absolute cursor-pointer rounded-md inset-0 focus-within:outline-none focus-within:ring-0 focus-within:ring-gray-400 focus-within:ring-offset-0">
                        <input type="file" name="file" id="fileInput" class="sr-only" @change="(e: any) => onUploadFile(e.target.files[0])"
                            ref="fileInput" accept=".xlsx, .xls, .csv"/>
                        <div v-if="isDraggedFile" class="text-2xl text-gray-500 h-full flex justify-center items-center">
                            Drop your file here
                        </div>
                    </label>
                    <div v-if="!isDraggedFile" class="text-center text-gray-500">
                        <FontAwesomeIcon icon="fal fa-file" class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                        <div class="mt-2 flex justify-center text-lg font-medium leading-6 ">
                            <p class="pl-1">{{ trans("Upload file") }}</p>
                        </div>
                        <div class="flex w-fit mx-auto text-sm leading-6 ">
                            <p class="">{{ trans("Click here or drag & drop on this zone") }}</p>
                        </div>
                        <p class="text-xs">
                            {{ trans(".csv, .xls, .xlsx") }}
                        </p>
                    </div>

                    <div class="absolute bottom-2 right-2 text-xxs flex items-center gap-x-1 text-gray-500 hover:text-gray-600 italic">
                        <label for="include_stored_item" class="select-none cursor-pointer">Include stored items</label>
                        <input v-model="isIncludeStoreItems" id="include_stored_item" type="checkbox"
                            class="h-3.5 w-3.5 rounded-sm text-indigo-600 focus:ring-0 cursor-pointer"/>
                    </div>
                </div>

                <!-- Section: Loading state (if upload progress) -->
                <div v-else class="text-center">
                    <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin h-8' aria-hidden='true' />
                    <p class="text-gray-500">Uploading..</p>
                </div>
            </div>

            <!-- Table History -->
             <div class="order-last flex items-start gap-x-2 gap-y-2 flex-col">
                <div class="text-sm text-gray-600"> {{ trans('Recent uploaded') + ` ${propName}:` }} </div>
                <div v-if="!isLoadingHistory" class="flex flex-wrap gap-x-2 gap-y-2">
                    <template v-if="[...dataHistoryFileUpload, ...echo().recentlyUploaded].length">
                        <template v-for="(history, index) in [...dataHistoryFileUpload, ...echo().recentlyUploaded]" :key="index">
<!--                            <Link-->
<!--                                :href="history?.view_route?.name-->
<!--                                    ? route(history.view_route.name, history.view_route.parameters)-->
<!--                                    : route(dataHistoryFileUpload[0].view_route.name, {...dataHistoryFileUpload[0].view_route.parameters, upload: history.action_id})"-->
<!--                            >-->
                                <div class="relative w-36 ring-1 ring-gray-300 rounded px-2 pt-2.5 pb-1 flex flex-col justify-start"
                                    :class="history?.view_route?.name ? 'bg-white hover:bg-gray-100 border-t-[3px] border-gray-500 cursor-pointer' : ' bg-lime-50/50 hover:bg-lime-100/70 border-t-[3px] border-lime-400'"
                                >
                                    <p class="text-lg leading-none text-gray-700 font-semibold">
                                        {{ history.number_rows ?? history.total }} <span class="text-xs text-gray-500 font-normal">rows</span>
                                    </p>
                                    <div class="flex gap-x-2">
                                        <span class="text-lime-600 text-xxs">{{ history.number_success ?? history.data.number_success }} success,</span>
                                        <span class="text-red-500 text-xxs">{{ history.number_fails ?? history.data.number_fails }} fails</span>
                                    </div>
                                    <span class="text-gray-400 text-xxs mt-2">{{ useFormatTime(history.uploaded_at ?? history.start_at, { formatTime: 'hms'}) }}</span>
                                </div>
<!--                            </Link>-->
                        </template>
                    </template>
                    <div v-else class="text-gray-500 text-xs">
                        {{ trans("No previous uploads") }}
                    </div>
                </div>
                <div v-else class="flex flex-wrap gap-x-2 gap-y-2">
                    <div v-for="(history, index) in 4" :key="index" class="w-36 h-20 skeleton rounded" />
                </div>
            </div>
        </div>

        <div v-if="errorMessage" class="mt-1 text-red-500 text-xs italic">
            *{{ errorMessage }}
        </div>
    </Modal>
</template>
