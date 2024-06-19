div<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 02 Oct 2023 03:26:44 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Tabs from "@/Components/Navigation/Tabs.vue"
import Input from '@/Components/Forms/Fields/Input.vue'
import { computed, ref, Ref, reactive } from "vue"
import { trans } from 'laravel-vue-i18n'
import Select from '@/Components/Forms/Fields/Primitive/PrimitiveSelect.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faImagePolaroid, faCloudUpload, faTimes } from '@fal'
import { faArrowRight } from '@far'
import { faSpinnerThird } from '@fad'
import { library } from '@fortawesome/fontawesome-svg-core'

import { useTabChange } from "@/Composables/tab-change"
import { capitalize } from "@/Composables/capitalize"
import Button from '@/Components/Elements/Buttons/Button.vue'
import { get } from 'lodash'
import Image from '@/Components/Image.vue'


import { watch } from 'vue'
import Modal from '@/Components/Utils/Modal.vue'
import axios from 'axios'
import TableUploadedImages from "@/Components/Tables/TableUploadedImages.vue";
import TableStockImages from "@/Components/Tables/TableStockImages.vue";

library.add(faImagePolaroid, faCloudUpload, faSpinnerThird, faTimes, faArrowRight)

const props: any = defineProps<{
    pageHead: any
    tabs: {
        current: string
        navigation: object
    }
    title: string
    uploaded_images?: object
    stock_images?: object
}>()

let currentTab = ref(props.tabs.current)
const handleTabUpdate = (tabSlug: string) => useTabChange(tabSlug, currentTab)

// Component: Tabs
const component = computed(() => {
    const components: any = {
        uploaded_images: TableUploadedImages,
        stock_images: TableStockImages,
    }
    return components[currentTab.value]
})

const selectedImages: {uploaded_images: number[], stock_images: number[]} = reactive({
    uploaded_images: [],
    stock_images: []
})
const isSelectImage = ref(false)
const loadingState = ref(false)
const websitesList = ref([])
const isModalOpen = ref(false)
const fieldWebsite = ref()
const fieldName = ref()

// To flat the 2 array (only the image id)
const compCombinedImages: Ref<any> = computed(() => {
    let abcdef = Object.values(selectedImages).reduce((accumulator: any, currentValue) => {
        if (Array.isArray(currentValue)) {
            return accumulator.concat(currentValue)
        } else {
            return accumulator
        }
    }, [])
    return abcdef
})

const compWebsitesList = computed(() => {
    return websitesList.value.map(obj => { return obj.slug })
})

// Retrieve the image object from image id
const compSelectedImage = computed(() => {
    const allImage = [...get(props, ["uploaded_images", 'data'], []), ...get(props, ['stock_images', 'data'], [])];
    let allImageTemp = allImage.filter((item) => compCombinedImages.value.includes(item.id))
    return allImageTemp
})

// On click create in Modal
const createBanner = async () => {
    loadingState.value = true
    try {
        if (fieldWebsite.value) {
            await axios.post(
                route('customer.models.banner.store.from-gallery', fieldWebsite.value),
                {
                    images: compCombinedImages.value,
                    name: fieldName.value
                },
                {
                    headers: { "Content-Type": "multipart/form-data" },
                }
            )

            loadingState.value = false
            setTimeout(() => {
                isModalOpen.value = false
            }, 1000)
        }

        if (!fieldWebsite.value) {
            await axios.post(
                route('customer.models.banner.store.from-gallery'),
                {
                    images: compCombinedImages.value,
                    name: fieldName.value
                },
                {
                    headers: { "Content-Type": "multipart/form-data" },
                }
            )

            loadingState.value = false
            setTimeout(() => {
                isModalOpen.value = false
            }, 1000)
        }
    } catch (error: any) {
        // console.error("===========================")
        console.error(error.message)
        loadingState.value = false
    }
}

// Modal: delete Selected Images
const deleteImageSelected = (imageId: number) => {
    if(selectedImages.uploaded_images?.includes(imageId)){
        let indexDeletedImageId = selectedImages.uploaded_images.indexOf(imageId)
        indexDeletedImageId !== -1 ? selectedImages.uploaded_images.splice(indexDeletedImageId, 1) : ''
    } else {
        let indexDeletedImageId = selectedImages.stock_images.indexOf(imageId)
        indexDeletedImageId !== -1 ? selectedImages.stock_images.splice(indexDeletedImageId, 1) : ''
    }
}

// Fetch website list on Modal
watch(isModalOpen, async () => {
    try {
        const response = await axios.get(route('customer.portfolio.websites.index'))
        websitesList.value = response.data.data
        loadingState.value = false
    } catch (error) {
        console.log(error)
        loadingState.value = false
    }
})


</script>

<template layout="CustomerApp">
    <!--suppress HtmlRequiredTitleElement -->

    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button>
            <!-- Button: Initial state -->
            <!-- <Button v-if="!isSelectImage" @click="isSelectImage = true" size="xs" :style="`tertiary`" id="select-images">
                {{ trans('Choose images for a new banner') }}
            </Button> -->

            <!-- Button: Create Banner -->
            <!-- <div v-if="isSelectImage" class="flex gap-x-2">
                <Button :style="'delete'" @click="isSelectImage = false"  size="xs" id="cancel-select">
                    <FontAwesomeIcon icon='fal fa-times' class='' aria-hidden='true' />
                    {{ trans('Cancel') }}
                </Button>
                <Button :key="compCombinedImages.length" size="xs" :style="compCombinedImages.length > 0 ? 'primary' : 'disabled'"
                    :class="[compCombinedImages.length > 0 ? '' : 'cursor-not-allowed']"
                    @click="compCombinedImages.length > 0 ? isModalOpen = true : false" id="create-banner">
                    {{ trans('Next') }} ({{ compCombinedImages.length }})
                    <FontAwesomeIcon v-if="compCombinedImages.length" icon='far fa-arrow-right' class='' aria-hidden='true' />
                </Button>
            </div> -->
        </template>
    </PageHeading>

    <!-- Tabs -->
    <Tabs :current="currentTab" :navigation="tabs['navigation']" @update:tab="handleTabUpdate" :selectedRow="selectedImages"
        :isSelectImage="isSelectImage">
        <template #addTitle="{ tabSlug }">
            {{
                isSelectImage
                ? selectedImages[tabSlug]?.length
                    ? trans(`(${selectedImages[tabSlug]?.length})`)
                    : trans(`(0)`)
                : ''
            }}
        </template>
    </Tabs>

    <!-- Content: Table from the Tab -->
    <KeepAlive>
        <component :isSelectImage="isSelectImage" :is="component" :key="currentTab"
            @selectedRow="(value: any) => selectedImages[currentTab] = value[currentTab]" :tab="currentTab"
            :data="props[currentTab]" />
    </KeepAlive>

    <!-- Popup: select Website to create Banner -->
    <Modal :isOpen="isModalOpen" @onClose="isModalOpen = false" width="w-2/5">
        <div class="flex flex-col gap-y-4 text-gray-700">
            <!-- Field -->
            <div class="flex flex-col gap-y-4">
                <div class="max-w-full">
                    <!-- Field: Website -->
                    <div>{{ trans('Select website') }}</div>
                    <Select :value="fieldWebsite" :fieldData="{ options: compWebsitesList }"
                        @onChange="(newValue) => fieldWebsite = newValue" />
                </div>


                <!-- Field: Name -->
                <div class="max-w-full">
                    <div>{{ trans('Name') }}</div>
                    <input v-model.trim="fieldName" placeholder="Enter name for new banner"
                        class="block w-full shadow-sm rounded-md text-gray-600 focus:ring-gray-500 focus:border-gray-500 sm:text-sm border-gray-300 read-only:bg-gray-100 read-only:ring-0 read-only:ring-transparent read-only:text-gray-500" />
                </div>
            </div>

            <div class="max-w-full">
                Images Banner
                <!-- <pre>{{ compCombinedImages }}</pre> -->
                <div class="flex flex-wrap gap-x-2 gap-y-2">
                    <div v-for="image in compSelectedImage" :key="image.id" class="relative">
                        <Image :src="image.thumbnail" class="flex items-center justify-center h-7 shadow " />
                        <div
                            class="cursor-pointer absolute top-0 text-xs right-0 translate-x-1/2 -translate-y-1/2 flex items-center justify-center px-1 bg-gray-200 hover:bg-gray-300 p-1 text-red-500 rounded-full h-2.5 w-2.5"
                            @click="() => deleteImageSelected(image.id)">
                            <FontAwesomeIcon :icon="['fal', 'times']" class="text-[7px] leading-none"/>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Button: Create -->
            <Button @click="createBanner" class="flex justify-center">
                <div class="px-3 relative flex items-center justify-center">
                    <span :class="{ 'opacity-0': loadingState }">{{ trans('Create') }}</span>
                    <FontAwesomeIcon icon='fad fa-spinner-third' class="w-5 h-5 absolute ml-1 animate-spin"
                        :class="{ 'opacity-0': !loadingState }" aria-hidden='true' />
                </div>
            </Button>
        </div>
    </Modal>
</template>


