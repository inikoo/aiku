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
import { Link, router } from "@inertiajs/vue3"
import { useEchoGrpPersonal } from '@/Stores/echo-grp-personal'
import PureInput from '../Pure/PureInput.vue'
import Papa from 'papaparse'
import Button from '@/Components/Elements/Buttons/Button.vue'
library.add(falFile, faTimes, faFileDownload, faDownload, faInfoCircle)

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
    // modelValue: boolean
    scope?: string
    title?: {
        label?: string
        information?: string
    }

    upload_spreadsheet: UploadSpreadsheet
    // isUploaded: boolean
}>()

const model = defineModel()


// const emits = defineEmits();

// const { isUploaded } = toRefs(props)

const isLoadingUpload = ref(false)
const dataHistoryFileUpload: any = ref([])
const isLoadingHistory = ref(false)
const isDraggedFile = ref(false)
const errorMessage = ref<string | null>(null)
const isIncludeStoreItems = ref(false)

const selectedFile = ref<File | null>(null)
const csvData = ref<string[]>([])

// Running when file is uploaded or dropped
const onUploadFile = async (fileUploaded: File) => {
    const fileExtention = fileUploaded?.name?.split('.')?.pop()?.toLowerCase()  // csv, xlsx, xls
    errorMessage.value = null

    if (fileExtention === 'csv' || fileExtention === 'xlsx' || fileExtention === 'xls') {
        selectedFile.value = fileUploaded
        Papa.parse(fileUploaded, {
            header: false,
            skipEmptyLines: true,
            complete: (results: { data: [] }) => {
                csvData.value = results.data
            },
            error: (error: any) => {
                console.error('Error parsing CSV:', error)
            }
        })
    } else {
        errorMessage.value = trans('File extension is not one of these:')+' .csv, .xlsx, .xls'
    }

    // console.log('aa', fileUploaded)

}

// Method: submit the selected file to server
const submitUpload = async () => {
    isDraggedFile.value = false
    errorMessage.value = null
    isLoadingUpload.value = true
    try {
        const aaa = await axios.post(
            route(props.upload_spreadsheet?.route?.upload?.name, props.upload_spreadsheet?.route?.upload?.parameters),
            {
                file: selectedFile.value,
                stored_item: isIncludeStoreItems.value
            },
            {
                headers: { "Content-Type": "multipart/form-data" },
            }
        )
        // console.log('aaa', aaa)
        useEchoGrpPersonal().isShowProgress = true

    } catch (error: any) {
        // console.log(error.response.data.message)
        errorMessage.value = error?.response?.data?.message
    }
    isLoadingUpload.value = false
}

// Method: refresh all like new open the modal
const clearAll = () => {
    selectedFile.value = null
    csvData.value = []
    errorMessage.value = null
}

const closeModal = () =>{
 /*    useEchoGrpPersonal().isShowProgress = false */
    useEchoGrpPersonal().isShowProgress = false
    model.value = false
}

// Fetch data history when Modal is opened
watch(model, async (newVal) => {
    if (props.upload_spreadsheet?.route?.history?.name) {
        isLoadingHistory.value = true
        if(newVal && !dataHistoryFileUpload.value.length) {  // to prevent fetch every modal appear
                console.log(props.upload_spreadsheet?.route)
            try {
                const data = await axios.get(route(props.upload_spreadsheet?.route?.history?.name, props.upload_spreadsheet?.route?.history?.parameters))
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
    <Modal :isOpen="model" @onClose="() => closeModal()" :closeButton="true">
        <div class="flex flex-col justify-between h-[500px] overflow-y-auto pb-4 px-1">
            <div>
                <!-- Title -->
                <div class="flex justify-center py-2 text-gray-600 font-medium mb-3">
                    <div>
                        <div class="flex gap-x-0.5">
                            {{ title?.label }}
                            <VTooltip v-if="title?.information" class="w-fit">
                                <FontAwesomeIcon icon='fad fa-info-circle' size="xs" class='text-gray-500' fixed-width
                                    aria-hidden='true' />
                                <template #popper>
                                    <div class="min-w-20 w-fit max-w-52 text-xs">
                                        {{ title?.information }}
                                    </div>
                                </template>
                            </VTooltip>
                        </div>
                        <div class="flex justify-center">
                            <a v-if="upload_spreadsheet?.route?.download?.name" :href="route(upload_spreadsheet?.route?.download?.name, upload_spreadsheet?.route?.download?.parameters)"
                                class="group text-xs text-gray-600 cursor-pointer px-2 w-fit" download>
                                <span class="text-xs text-gray-400 group-hover:text-gray-600">
                                    <FontAwesomeIcon icon='fas fa-file-download' class='text-gray-400 group-hover:text-gray-600'
                                        aria-hidden='true' />
                                    {{ upload_spreadsheet?.template?.label || trans(`Download template .xlsx`) }}
                                </span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Section: Upload box -->
                <div class="grid gap-x-3 px-1">
                    <div @drop="(e: any) => (e.preventDefault(), onUploadFile(e.dataTransfer.files[0]))" @dragover.prevent
                        @dragenter.prevent @dragleave.prevent
                        class="relative max-w-full flex items-center justify-center rounded-lg border border-dashed border-gray-700/25 px-6 py-3 bg-gray-400/10"
                        :class="[
                            {'hover:bg-gray-400/20': !isLoadingUpload},
                            errorMessage ? 'errorShake' : ''
                        ]">
                        <!-- Section: Upload area -->
                        <div v-if="selectedFile" class="text-gray-500 flex flex-col items-center gap-y-2">
                            <div class="flex items-center gap-x-1">
                                <FontAwesomeIcon icon="fal fa-file" class="mx-auto h-5 w-5 text-gray-300"
                                    aria-hidden="true" />
                                {{ selectedFile?.name }}
                            </div>
                            <Button @click="() => clearAll()" label="Remove file" type="negative" size="s" />
                        </div>

                        <div v-else-if="!isLoadingUpload" class="">
                            <label for="fileInput"
                                class="absolute cursor-pointer rounded-md inset-0 focus-within:outline-none focus-within:ring-0 focus-within:ring-gray-400 focus-within:ring-offset-0">
                                <input type="file" name="file" id="fileInput" class="sr-only"
                                    @change="(e: any) => onUploadFile(e.target.files[0])" ref="fileInput"
                                    accept=".xlsx, .xls, .csv" />
                                <div v-if="isDraggedFile"
                                    class="text-2xl text-gray-500 h-full flex justify-center items-center">
                                    Drop your file here
                                </div>
                            </label>

                            <div v-if="!isDraggedFile" class="text-center text-gray-500">
                                <div class="flex justify-center text-sm font-medium leading-6 ">
                                    {{ trans("Upload file") }}
                                </div>
                                <div class="flex w-fit mx-auto text-xs leading-6 ">
                                    <p class="">{{ trans("Drag and drop, or browse your files") }} (.csv, .xlx, .xlsx)</p>
                                </div>
                            </div>

                            <div class="absolute bottom-2 right-2 text-xxs flex items-center gap-x-1 text-gray-500 hover:text-gray-600 italic">
                                <label for="include_stored_item" class="select-none cursor-pointer">Include stored items</label>
                                <input v-model="isIncludeStoreItems" id="include_stored_item" type="checkbox"
                                    class="h-3.5 w-3.5 rounded-sm text-indigo-600 focus:ring-0 cursor-pointer" />
                            </div>
                        </div>

                        <!-- Section: Loading state (if upload progress) -->
                        <!-- <div v-else class="text-center">
                            <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin h-8' aria-hidden='true' />
                            <p class="text-gray-500">Uploading..</p>
                        </div> -->

                    </div>

                <div v-if="errorMessage" class="mt-1 text-red-500 text-xs italic">
                    *{{ errorMessage }}
                </div>

                    <!-- Section: Excel preview -->
                    <Transition name="headlessui">
                        <div v-if="csvData?.length" class="text-xxs mt-3 max-w-3xl">
                            <div class="text-sm py-1 pl-3">Preview your data</div>
                            <div class="max-w-full border border-gray-300 rounded-md overflow-hidden">
                                <table class="w-full">
                                    <thead class="">
                                        <tr class="bg-green-400 rounded-t-xl border border-green-700">
                                            <th v-for="(header, index) in csvData[0]" :key="index"
                                                class="whitespace-nowrap overflow-ellipsis pl-3"
                                                :class="upload_spreadsheet?.required_fields?.length ? upload_spreadsheet?.required_fields.includes(header) ? 'bg-green-400' : 'bg-red-300 hover:bg-red-400' : 'bg-gray-100'"
                                                v-tooltip="upload_spreadsheet?.required_fields?.includes(header) ? undefined : 'This column will not processed'"
                                            >
                                                {{ header }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, rowIndex) in csvData.slice(1, 6)" :key="rowIndex" class="border border-gray-300">
                                            <td v-for="(cell, cellIndex) in row" :key="cellIndex" class="pl-3">{{ cell }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-if="csvData?.length > 6" class="py-1 text-center bg-gray-100">
                                    and {{ csvData.length-6 }} more
                                </div>
                            </div>
                            <div class="flex justify-end mt-3 w-full">
                                <Button @click="() => submitUpload()" label="Submit" size="s" :loading="isLoadingUpload" />
                            </div>
                        </div>
                    </Transition>

                </div>
            </div>

            <!-- Section: table history -->
            <div class="flex items-start gap-x-2 gap-y-2 flex-col mt-4">
                <div class="text-sm text-gray-600"> {{ trans('Recent uploaded') + ` ${scope}:` }} </div>
                <div v-if="!isLoadingHistory" class="flex flex-wrap gap-x-2 gap-y-2">
                    <template v-if="[...dataHistoryFileUpload, ...useEchoGrpPersonal().recentlyUploaded].length">
                        <template v-for="(history, index) in [...dataHistoryFileUpload, ...useEchoGrpPersonal().recentlyUploaded]"
                            :key="index">
                            <!--                            <Link-->
                            <!--                                :href="history?.view_route?.name-->
                            <!--                                    ? route(history.view_route.name, history.view_route.parameters)-->
                            <!--                                    : route(dataHistoryFileUpload[0].view_route.name, {...dataHistoryFileUpload[0].view_route.parameters, upload: history.action_id})"-->
                            <!--                            >-->
                            <div class="relative w-36 ring-1 ring-gray-300 rounded px-2 pt-2.5 pb-1 flex flex-col justify-start"
                                :class="history?.view_route?.name ? 'bg-white hover:bg-gray-100 border-t-[3px] border-gray-500 cursor-pointer' : ' bg-lime-50/50 hover:bg-lime-100/70 border-t-[3px] border-lime-400'">
                                <p class="text-lg leading-none text-gray-700 font-semibold">
                                    {{ history.number_rows ?? history.total }} <span
                                        class="text-xs text-gray-500 font-normal">rows</span>
                                </p>
                                <div class="flex gap-x-2">
                                    <span class="text-lime-600 text-xxs">{{ history.number_success ??
                                        history.data.number_success }} success,</span>
                                    <span class="text-red-500 text-xxs">{{ history.number_fails ??
                                        history.data.number_fails }} fails</span>
                                </div>
                                <span class="text-gray-400 text-xxs mt-2">{{ useFormatTime(history.uploaded_at ??
                                    history.start_at, { formatTime: 'hms'}) }}</span>
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

    </Modal>
</template>
