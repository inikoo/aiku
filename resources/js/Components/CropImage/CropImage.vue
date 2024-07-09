<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Sun, 23 Jul 2023 22:01:23 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, defineEmits } from 'vue';
import "swiper/css"
import "swiper/css/navigation"
import axios from 'axios'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSpinnerThird } from '@fad'
import { faExclamation } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExclamation, faSpinnerThird)

import { trans } from "laravel-vue-i18n"
import Button from "@/Components/Elements/Buttons/Button.vue"
import CropComponents from "./CropComponents.vue"
import { routeType } from '@/types/route'


const props = withDefaults(defineProps<{
    data: File[];
    imagesUploadRoute: string
    response : Function
    ratio?:  {
        w: number
        h: number
    },
}>(), {
    ratio: { w : 4, h : 1 }
})

const emits = defineEmits<{
    (e: 'onFinishCropped', image: any): void
}>()

const setData2 = () => {
    const data = []
    for (const set of props.data) {
        console.log('ss',set)
        data.push({
            originalFile: set,
        })
    }
    return data
}

const setData = ref(setData2())

const generateThumbnail = (file: {originalFile: File, imagePosition: any}) => {
    if (
        file.originalFile &&
        file.originalFile instanceof File &&
        !file.imagePosition
    ) {
        let fileSrc = URL.createObjectURL(file.originalFile);
        setTimeout(() => {
            URL.revokeObjectURL(fileSrc);
        }, 200)
        return fileSrc
    } else if (file.imagePosition) {
        return file.imagePosition.canvas.toDataURL()
    } else {
        return file.originalFile
    }
};

const form = ref(new FormData())
const catchError = ref()
const loadingState = ref(false)

const addComponent = async () => {
    loadingState.value = true
    const SendData = []

    const processItem = async (item) => {
        return new Promise((resolve, reject) => {
            if (item.imagePosition) {
                item.imagePosition.canvas.toBlob((blob) => {
                    // SendData.push(blob)
                    form.value.append("blob", blob, item.originalFile.name)
                    resolve()
                })
            } else if (item.originalFile.type == 'image/gif') {
                form.value.append("gif", item.originalFile)
                resolve()
            } else {
                resolve()
            }
        });
    };

    await Promise.all(setData.value.map(processItem))
    
    for (const [key, value] of form.value.entries()) {
        SendData.push(value)
        // console.log((value.size / (1024 * 1024)).toFixed(2))
    }

    try {
        const response = await axios.post(
            props.imagesUploadRoute,
            { images: SendData },
            {
                headers: { "Content-Type": "multipart/form-data" },
            }
        );
        form.value = new FormData()
        props.response(response.data)
        emits('onFinishCropped', response.data)
        loadingState.value = false
    } catch (error) {
        console.log(error)
        form.value = new FormData()
        catchError.value = error
        // props.response(error.response)
        loadingState.value = false
    }
}

const current = ref(0)

const generateGif = (file: File) => {
    let fileSrc = URL.createObjectURL(file)
    setTimeout(() => {
        URL.revokeObjectURL(fileSrc)
    }, 1000)
    return fileSrc
}

</script>

<template>
    <!-- Preview cropped image -->
    <div class="mb-6 relative w-full flex justify-center">
        <div class="flex items-center border-2 border-gray-500 shadow-md"
            :class="[
                ratio ? `aspect-[${ratio.w}/${ratio.h}]` : 'aspect-[2/1] md:aspect-[3/1] lg:aspect-[4/1]',
                ratio.w == 4 ? 'w-full' : `w-${ratio.w}/4`
            ]"
        >
            <img :src="generateThumbnail(setData[current])" alt="" class="w-full h-full object-fit" />
        </div>
    </div>

    <!-- List: the uploaded images -->
    <div class="mb-6 space-y-3">
        <div class="max-w-full py-5 px-6 h-96 overflow-y-auto border border-solid border-gray-300 rounded-lg">
            <ul role="list"
                class="mx-auto grid max-w-full grid-cols-1 gap-x-8 gap-y-16 sm:grid-cols-2 lg:mx-0 lg:max-w-none lg:grid-cols-3"
            >
                <li v-for="(item, index) in setData" :key="index">
                    <div @click="current = index" :class="['p-2.5 border border-solid rounded-lg cursor-pointer ', setData[current] == item ?  'border-gray-400 bg-gray-200'  : 'border-gray-300']">
                        <CropComponents v-if="item.originalFile.type !== 'image/gif'" :data="item"  :ratio="ratio"/>
                        <div v-else> <img :src="generateGif(item.originalFile)" :alt="item.originalFile.name"></div>
                        <h3 class="flex justify-start align-middle leading-5 tracking-tight truncate w-full" :class="[setData[current] == item ? 'text-amber-500 font-semibold' : 'text-gray-500']">
                            {{ item.originalFile.name }}
                        </h3>
                    </div>
                </li>
            </ul>
        </div>
        <div v-if="catchError?.response" class="text-red-500">
            <FontAwesomeIcon icon='fas fa-exclamation' class='' aria-hidden='true' />
            {{ catchError.response.statusText}}
        </div>
    </div>
    <div class="">
        <Button
            :style="`primary`"
            icon="fas fa-upload"
            class="relative"
            :disabled="loadingState"
            size="xs"
            @click="addComponent"
        >
            {{ trans("Save Image") }}
        </Button>
        <FontAwesomeIcon v-if="loadingState" icon='fad fa-spinner-third' class='animate-spin ml-2' aria-hidden='true' />
    </div>
</template>

<style lang="scss" scoped>
.swiper {
    @apply w-full h-full;
}

.swiper-slide {
    @apply bg-gray-200;
    text-align: center;
    font-size: 18px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.swiper-slide img {
    @apply w-full h-auto;
    display: block;
    object-fit: cover;
}
</style>
