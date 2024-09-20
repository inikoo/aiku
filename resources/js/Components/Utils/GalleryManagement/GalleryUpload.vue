<script setup lang='ts'>
import FileUpload from 'primevue/fileupload'
import Badge from 'primevue/badge'
import Button from '@/Components/Elements/Buttons/Button.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faUpload, faImages } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { routeType } from '@/types/route'
library.add(faUpload, faImages)

const modelFiles = defineModel('files')

const props = defineProps<{
    uploadRoute: routeType
    useCrop?: boolean
    isLoading?: boolean
}>()

const emits = defineEmits<{
    (e: 'onFinishUpload'): void
    (e: 'onSubmitUpload'): void
}>()


const formatSize = (bytes: number) => {
    const kb = bytes / 1024;
    const mb = kb / 1024;
    const gb = mb / 1024;

    if (gb >= 0.95) {  // If close to 1 GB (95% or more)
        return `${gb.toFixed(2)} GB`;
    } else if (mb >= 1) {  // If 1 MB or more
        return `${mb.toFixed(2)} MB`;
    } else {  // If less than 1 MB, show in KB
        return `${kb.toFixed(2)} KB`;
    }
}

</script>

<template>
    <div class="relative">
        <!-- <ProgressBar :value="totalSizePercent" :showValue="false" class="md:w-20rem h-1 w-full md:ml-auto">
            <span class="whitespace-nowrap">{{ totalSize }}B / 1Mb</span>
        </ProgressBar>
        <br>{{ totalSize }} -->
        <FileUpload
            name="image"
            :url="uploadRoute"
            :multiple="true"
            accept="image/*"
            :maxFileSize="10000000"
            @upload="emits('onFinishUpload')"
            @select="(filess) => modelFiles = filess.files"
            
        >
            <template #header="{ chooseCallback, uploadCallback, clearCallback, files }">
                <div class="flex flex-wrap justify-center items-center flex-1 gap-4">
                    <Button @click="chooseCallback()" label="Choose" icon="fal fa-images" type="tertiary"></Button>
                    <Button @click="emits('onSubmitUpload')" label="Upload" icon="fal fa-upload" :loading="isLoading" :disabled="!files || files.length === 0"></Button>
                    <Button @click="clearCallback()" label="Clear" type="negative" :disabled="!files || files.length === 0"></Button>
                </div>
            </template>

            <template #content="{ files, uploadedFiles, removeUploadedFileCallback, removeFileCallback }">
                <div class="flex flex-col gap-8 pt-4">
                    <!-- Looping: pending files -->
                    <div v-if="files.length > 0">
                        <div class="flex flex-wrap gap-4">
                            <div v-for="(file, index) of files" :key="file.name + file.type + file.size"
                                class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
                                <div>
                                    <img role="presentation" :alt="file.name" :src="file.objectURL" width="100" height="50" />
                                </div>
                                <span class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">{{
                                    file.name }}</span>
                                <div>{{ formatSize(file.size) }}</div>
                                <Badge value="Pending" severity="warn" />
                                <Button icon="fal fa-times" type="negative" label="" @click="removeFileCallback(index)" />
                            </div>
                        </div>
                    </div>

                    <!-- Looping: completed files -->
                    <div v-if="uploadedFiles.length > 0">
                        <div class="flex flex-wrap gap-4">
                            <div v-for="(file, index) of uploadedFiles" :key="file.name + file.type + file.size"
                                class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
                                <div>
                                    <img role="presentation" :alt="file.name" :src="file.objectURL" width="100"
                                        height="50" />
                                </div>
                                <span class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">
                                    {{ file.name }}
                                </span>
                                <div>{{ formatSize(file.size) }}</div>
                                <Badge value="Completed" class="mt-4" severity="success" />
                                <Button icon="fal fa-times" type="negative" label="" @click="removeUploadedFileCallback(index)" />
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template #empty>
                <div class="flex items-center justify-center flex-col">
                    <FontAwesomeIcon icon='fal fa-upload' class='!border-2 !rounded-full !p-8 !text-4xl !text-muted-color' fixed-width aria-hidden='true' />
                    <p class="mt-6 mb-0">Drag and drop files to here to upload.</p>
                </div>
            </template>
        </FileUpload>
    </div>
</template>