<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { inject, ref } from 'vue'
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from '@headlessui/vue'
// import Upload from './Upload.vue'
// import StockImages from './StockImages.vue'
// import UploadedImages from "@/Components/Fulfilment/Website/Gallery/UploadedImages.vue"
// import Button from '@/Components/Elements/Buttons/Button.vue'
import GalleryUpload from '@/Components/Utils/GalleryManagement/GalleryUpload.vue'
import GalleryUploadedImages from '@/Components/Utils/GalleryManagement/GalleryUploadedImages.vue'

import { faCube, faStar, faImage } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { routeType } from '@/types/route'
import { router } from '@inertiajs/vue3'
import { notify } from '@kyvg/vue3-notification'
import { trans } from 'laravel-vue-i18n'
import { ImageData } from '@/types/Image'
library.add(faCube, faStar, faImage)

const layout = inject('layout', layoutStructure)
const props = withDefaults(defineProps<{
    width?: string
    uploadRoute: routeType
    stockImagesRoute?: routeType
    imagesUploadedRoutes?: routeType
    attachImageRoute: routeType
    tabs?: string[]
    useCrop?: boolean
    cropProps?: {}
    closePopup: Function
    maxSelected?: number
    uploadFileLimit?: number
    submitUpload?: Function
    isLoadingSubmit?: boolean
}>(), {
    tabs: () =>  ['upload', 'images_uploaded', 'stock_images'],
    useCrop: false,
    stockImagesRoute: () => ({
        name: 'grp.gallery.stock-images.index'
    }),
    imagesUploadedRoutes: () => ({
        name: 'grp.gallery.uploaded-images.index'
    }),
    cropProps: () => ({
        ratio: { w: 1, h: 1 }
    })
})

const selectedTab = ref(0)



const emits = defineEmits<{
    // (e: 'onClose'): void
    // (e: 'onSuccessUpload', data: []): void
    // (e: 'onUpload', value: {}): void
    (e: 'submitSelectedImages', value: ImageData[]): void
    (e: 'selectImage', value: {}): void
}>()

const tabsData = [
    {
        label: "Upload",
        key: 'upload',
    },
    {
        label: "Images Uploaded",
        key: 'images_uploaded',
    },
    {
        label: "Stock Images",
        key: 'stock_images',
    },
].filter((item) => props.tabs.includes(item.key))


// const getComponent = (componentName: string) => {
//     const components: any = {
//         'upload': GalleryUpload,
//         'images_uploaded': GalleryUploadedImages,
//         // 'stock_images': StockImages
//     }
//     return components[componentName] ?? null
// }

// const onUpload = (e) => {
//     emits('onUpload', e)
//     selectedTab.value = 1
// }

const isLoading = ref(false)
const selectedUploadFiles = ref([])

const onSubmitUpload = async (files: File[], clear: Function) => {
    const formData = new FormData()
    Array.from(files).forEach((file, index) => {
        formData.append(`images[${index}]`, file)
    })

    console.log('form', files, formData)

    router.post(route(props.uploadRoute.name, props.uploadRoute.parameters),
        formData,
        {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onStart: () => isLoading.value = true,
            onSuccess: (xxx) => {
                console.log('xxx', xxx)
                notify({
                    title: trans('Success'),
                    text: trans('New image added'),
                    type: 'success',
                }),
                clear()
            },
            onError: (ee) => {
                console.log('ee', ee)
                notify({
                    title: trans('Something went wrong'),
                    text: trans('Failed to add new image'),
                    type: 'error',
                })
            },
            onFinish: () => {
                isLoading.value = false
            }
        }
    )
}


</script>


<template>
    <div class="">
        <!-- {{ stockImageRoutes }} -->
        <TabGroup :selectedIndex="selectedTab" @change="(index) => selectedTab = index">
            <TabList class="flex space-x-8 border-b-2">
                <Tab v-for="tab in tabsData" as="template" :key="tab.key" v-slot="{ selected }">
                    <button
                        :style="selected ? { color: layout.app.theme[0], borderBottomColor: layout.app.theme[0] } : {}"
                        :class="[
                            'whitespace-nowrap border-b-2 py-1.5 px-1 text-sm font-medium focus:ring-0 focus:outline-none mb-2',
                            selected
                                ? `border-org-5s00 text-[${layout.app.theme[0]}]`
                                : `border-transparent text-[${layout.app.theme[0]}] hover:border-[${layout.app.theme[0]}]`,
                        ]">
                        {{ tab.label }}
                    </button>
                </Tab>
            </TabList>

            <TabPanels class="mt-2 h-[700px]">
                <TabPanel v-for="(tab, idx) in tabsData" :key="idx"
                    class="h-full rounded-xl bg-white p-3 ring-white/60 ring-offset-2 ring-offset-blue-400 focus:outline-none">
                    <GalleryUpload
                        v-if="tab.key === 'upload'"
                        :uploadRoute
                        :useCrop
                        :fileLimit="uploadFileLimit"
                        :isLoading="props.isLoadingSubmit || isLoading"
                        @onSubmitUpload="(files, clear) => submitUpload ? submitUpload(files, clear) : onSubmitUpload(files, clear)"
                    />

                    <GalleryUploadedImages
                        v-if="tab.key === 'images_uploaded'"
                        :imagesUploadedRoutes
                        :attachImageRoute
                        :closePopup
                        :maxSelected
                        @selectImage="(image: {}) => emits('selectImage', image)"
                        @submitSelectedImages="(images: ImageData[]) => emits('submitSelectedImages', images)"
                    />

                    <GalleryUploadedImages
                        v-if="tab.key === 'stock_images'"
                        :imagesUploadedRoutes="stockImagesRoute"
                        :attachImageRoute
                        :closePopup
                        :maxSelected
                        @selectImage="(image: {}) => emits('selectImage', image)"
                        @submitSelectedImages="(images: ImageData[]) => emits('submitSelectedImages', images)"
                    />

                </TabPanel>
            </TabPanels>
        </TabGroup>
    </div>
</template>