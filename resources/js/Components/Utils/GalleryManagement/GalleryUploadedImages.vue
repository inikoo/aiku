<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { ref, onBeforeMount } from "vue"
import axios from 'axios'
import Image from '@/Components/Image.vue'
import { notify } from '@kyvg/vue3-notification'
import { faSpinnerThird } from '@fad'
import EmptyState from "@/Components/Utils/EmptyState.vue"
import { routeType } from "@/types/route"
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import { trans } from "laravel-vue-i18n"
import { useTruncate } from "@/Composables/useTruncate"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { Images } from "@/types/Images"
import { router } from '@inertiajs/vue3'

library.add(faSpinnerThird)

// interface Image {
//     name: string
//     id: number
//     thumbnail: Images
//     size: string
//     created_at: string
// }

const props = defineProps<{
    imagesUploadedRoutes: routeType
    attachImageRoute: routeType
    closePopup: Function
}>()

const stockImagesList = ref<Images[]>([])
const selectedImages = ref<number[]>([])
const isLoading = ref<string | boolean>(false)

const emits = defineEmits<{
    (e: 'selectImage', value: {}): void
}>()

// Method: Fetch stock images
const getStockImages = async () => {
    try {
        isLoading.value = 'fetchStockImages'
        const response = await axios.get(route(props.imagesUploadedRoutes.name, {...props.imagesUploadedRoutes.parameters, perPage: 40}));
        stockImagesList.value = response.data.data
    } catch (error: any) {
        console.log('error', error);
        notify({
            title: trans('Something went wrong.'),
            text: trans('Cannot show stock images.'),
            type: 'error'
        })
    } finally {
        isLoading.value = false
    }
}

onBeforeMount(() => {
    getStockImages()
})

// Method: select and unselect image
const toggleImageSelection = (imageId: number) => {
    const index = selectedImages.value.indexOf(imageId);

    if (index > -1) {
        // If it exists, remove it
        selectedImages.value.splice(index, 1);
    } else {
        // If it doesn't exist, add it
        selectedImages.value.push(imageId);
    }
}

// Method: submit selected stock images
const submitSelectedImages = () => {
    router.post(
        route(props.attachImageRoute.name, props.attachImageRoute.parameters),
        {
            images: selectedImages.value
        },
        {
            onStart: () => isLoading.value = 'submitImage',
            onFinish: (aaa) => {
                isLoading.value = false
            },
            onSuccess: (zzz) => {
                selectedImages.value = [],
                props.closePopup()
            },
            onError: (err) => {
                notify({
                    title: trans('Something went wrong.'),
                    text: err?.message || '',
                    type: 'error',
                })
            }
        }
    )
}
</script>

<template>
    <div class="h-full relative overflow-y-auto isolate pr-4">
        <template v-if="isLoading !== 'fetchStockImages'">
            <div class="sticky top-0 pb-2 z-10 bg-white ">
                <div class="pb-2 flex justify-between border-b border-gray-300 ">
                    <div class="text-2xl font-semibold">
                        {{ trans('Select images') }} ({{ selectedImages.length }})
                    </div>
                    <Button label="Select image" @click="() => submitSelectedImages()" :loading="isLoading === 'submitImage'" />
                </div>
            </div>

            <div class="p-1 overflow-y-auto h-min grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 lg:grid-cols-8 gap-4">
                <template v-if="stockImagesList.length > 0">
                    <li v-for="image in stockImagesList" :key="image.name"
                        class="relative overflow-hidden ring-1 ring-gray-300 transition-transform duration-75 cursor-pointer rounded-md"
                        :class="[selectedImages.includes(image.id) ? 'scale-[97%]' : '']"
                        @click="() => toggleImageSelection(image.id)"    
                    >
                        <div v-if="selectedImages.includes(image.id)" class="absolute inset-0 bg-blue-500/20" 
                        />
                        <div class="bg-gray-200 aspect-[3/2] w-full object-cover ">
                            <Image
                                :src="image.thumbnail"
                                :alt="image.created_at"
                                @click="() => emits('selectImage', image)"
                            />
                        </div>
                        <div class="p-2">
                            <h3 class="font-medium tracking-tight truncate">{{ image.name }}</h3>
                            <p class="text-sm text-gray-400">{{ image.size }}</p>
                        </div>
                    </li>
                    <!-- <div v-for="image in stockImages" :key="image.id" class="overflow-hidden duration-300" >
                        <div class="border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow aspect-h-1 aspect-w-1 w-full bg-gray-200" @click="()=>emits('pick',image)">
                            <Image :src="image.thumbnail" class="w-full object-cover object-center group-hover:opacity-75" />
                        </div>
                        <div class="text-xs">{{ useTruncate(image.name, 20) }}</div>
                        <div class="text-xs">{{ image.size }}</div>
                    </div> -->
                </template>

                <div v-else class="flex justify-center col-span-4">
                    <EmptyState :data="{ title : trans('You dont have image'), description : ''}"/>
                </div>
            </div>

        </template>
        <div v-else class="flex justify-center items-center">
            <!-- <FontAwesomeIcon icon='fad fa-spinner-third' class='animate-spin' fixed-width  aria-hidden="true"/> -->
            <LoadingIcon class="text-4xl" />
        </div>
    </div>
</template>
