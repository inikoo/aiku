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
import UploadedImages from "@/Components/Fulfilment/Website/Gallery/UploadedImages.vue"
import Button from '@/Components/Elements/Buttons/Button.vue'
import GalleryUpload from '@/Components/Utils/GalleryUpload.vue'

import { faCube, faStar, faImage } from "@fas"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import axios from 'axios'
library.add(faCube, faStar, faImage)

const props = withDefaults(defineProps<{
    width?: string
    uploadRoute: string
    stockImageRoutes?: routeType
    imagesUploadedRoutes?: routeType
    tabs?: string[]
    useCrop?: boolean
    cropProps?: Object
}>(), {
    tabs: ['upload', 'images_uploaded', 'stock_images'],
    useCrop: false,
    stockImageRoutes: {
        name: 'grp.gallery.stock-images.index',
        parameters: ""
    },
    imagesUploadedRoutes: {
        name: 'grp.gallery.uploaded-images.index',
        parameters: ""
    },
    cropProps: {
        ratio: { w: 1, h: 1 }
    }
})

const layout = inject('layout', layoutStructure)
const selectedTab = ref(0)



const emits = defineEmits<{
    (e: 'onClose'): void
    (e: 'onSuccessUpload', data: []): void
    (e: 'onUpload', value: Object): void
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


const getComponent = (componentName: string) => {
    const components: any = {
        'upload': GalleryUpload,
        'images_uploaded': UploadedImages,
        // 'stock_images': StockImages
    }
    return components[componentName] ?? null
}

const onUpload = (e) => {
    emits('onUpload', e)
    selectedTab.value = 1
}

const isLoading = ref(false)
const selectedUploadFiles = ref([])
const forfdatra = (formData) => {
    console.log('formData', formData)
    
    const files = formData.getAll('image');

    files.forEach((file, index) => {
        console.log('index', index)
        formData.delete('image'); // Remove the original 'image[]' entry
        // formData.append(`images[${index}]`, file); // Append with the new key format
    });

    console.log('formData', formData)
}
const onSubmitUpload = async () => {
    isLoading.value = true
    const formData = new FormData()
    Array.from(selectedUploadFiles.value).forEach((file, index) => {
        formData.append(`images[${index}]`, file)
    })

    console.log('formData', formData)
    try {
        const response = await axios.post(props.uploadRoute, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: function (progressEvent) {
            // Calculate the progress percentage
            const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            console.log(percentCompleted + '%'); // You can update your UI with the progress here
        }
        })
        emits('onSuccessUpload', response.data)
        console.log('response', response)
    } catch (error) {
        console.error('error', error)
    } finally {
        isLoading.value = false
    }
}

</script>


<template>
    <div class="h-[800px]">
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

            <TabPanels class="mt-2">
                <TabPanel v-for="(tab, idx) in tabsData" :key="idx" :class="[
                    'rounded-xl bg-white p-3 overflow-auto',
                    'ring-white/60 ring-offset-2 ring-offset-blue-400 focus:outline-none',
                ]">
                    <component
                        v-model:files="selectedUploadFiles"
                        :is="getComponent(tab['key'])"
                        :uploadRoute
                        :imagesUploadedRoutes="imagesUploadedRoutes"
                        :useCrop="useCrop"
                        :cropProps="cropProps"
                        :isLoading
                        :stockImageRoutes="stockImageRoutes"
                        @onUpload="onUpload"
                        @onSubmitUpload="onSubmitUpload"
                    />

                </TabPanel>
            </TabPanels>
        </TabGroup>
    </div>
</template>