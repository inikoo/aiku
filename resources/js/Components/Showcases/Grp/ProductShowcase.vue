<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import GalleryManagement from "@/Components/Utils/GalleryManagement.vue"
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { notify } from '@kyvg/vue3-notification'
import Image from "@/Components/Image.vue"
import Modal from "@/Components/Utils/Modal.vue"
import {
    Tab,
    TabGroup,
    TabList,
    TabPanel,
    TabPanels,
} from '@headlessui/vue'
import { inject, ref } from 'vue'
import EmptyState from "@/Components/Utils/EmptyState.vue"
import axios from "axios";
import { aikuLocaleStructure } from "@/Composables/useLocaleStructure"

import { faTrash as falTrash, faEdit } from '@fal'
import { faCircle, faTrash } from '@fas'
import LoadingIcon from '@/Components/Utils/LoadingIcon.vue'
import { remove as removeLodash } from 'lodash'
import { useFormatTime } from '../../../Composables/useFormatTime'
import { trans } from 'laravel-vue-i18n'
library.add(faCircle, faTrash, falTrash, faEdit)

const props = defineProps<{
    data: {
        product
    }
}>()


const locale = inject('locale', aikuLocaleStructure)
const selectedImage = ref(0)
const isLoading = ref<string[]>([])

const stats = [
    { name: '2024', stat: '71,897', previousStat: '70,946', change: '12%', changeType: 'increase' },
    { name: '2023', stat: '58.16%', previousStat: '56.14%', change: '2.02%', changeType: 'increase' },
    { name: '2022', stat: '24.57%', previousStat: '28.62%', change: '4.05%', changeType: 'decrease' },
    { name: '2021', stat: '71,897', previousStat: '70,946', change: '12%', changeType: 'increase' },
    { name: '2020', stat: '58.16%', previousStat: '56.14%', change: '2.02%', changeType: 'increase' },
    { name: '2019', stat: '24.57%', previousStat: '28.62%', change: '4.05%', changeType: 'decrease' },
]

const product = ref({
    images: props.data.product.data.images,
})

const deleteImage = async (data, index) => {
    isLoading.value.push(data.id)

    try {
        const response = await axios.delete(route(props.data.deleteImageRoute.name, {
            ...props.data.deleteImageRoute.parameters, media: data.id
        }));
        if(selectedImage.value == index) selectedImage.value = 0
        product.value.images.splice(index,1)
    } catch (error: any) {
        console.log('error', error);
        notify({
            title: 'Failed',
            text: 'cannot show stock images',
            type: 'error'
        })
    } finally {
        isLoading.value.push(data.id)
        removeLodash(isLoading.value, (n) => {
            return n == data.id
        })
    }
}


function changeSelectedImage(index) {
    selectedImage.value = index
}


const isModalGallery = ref(false)

</script>


<template>
    <div class="grid grid-cols-4 gap-x-1 gap-y-4">
        <div class="p-5 space-y-5">
            <div class="relative">
                <div class=" h-full aspect-square rounded-lg shadow">
                    <!-- Section: Gallery -->
                    <TabGroup as="div" class="flex flex-col p-2.5" :selectedIndex="selectedImage" @change="changeSelectedImage">
                        <!-- Section: Main image (big) -->
                        <TabPanels class="overflow-hidden duration-300">
                            <template v-if="product.images.length > 0">
                                <TabPanel v-for="image in product.images" :key="image.id">
                                    <div class="relative flex items-center border border-gray-200 rounded-lg aspect-square w-auto h-auto overflow-hidden">
                                        <div class="absolute top-1 right-3 flex items-center gap-2 capitalize"
                                            :class="data.product.data.state === 'active' ? 'text-green-500' : ''"
                                        >
                                            <FontAwesomeIcon v-if="data.product.data.state === 'active'" icon='fas fa-circle' class='text-xs animate-pulse' fixed-width aria-hidden='true' />
                                            {{ data.product.data.state }}
                                        </div>
                                        <Image :src="image.source" :alt="image.name" class="" />
                                    </div>
                                </TabPanel>
                            </template>

                            <template v-else>
                                <TabPanel>
                                    <EmptyState
                                        :data="{ title: 'You don\'t have any images', description: 'Click to upload' }"
                                        @click="isModalGallery = true"
                                        class="cursor-pointer hover:bg-gray-50"    
                                    />
                                </TabPanel>
                            </template>
                        </TabPanels>
                        
                        <!-- Section: Images list -->
                        <div @scroll="(eee) => console.log('on scroll', eee)" class="h-56 overflow-y-auto mx-auto mt-6 hidden w-full max-w-2xl sm:block lg:max-w-none">
                            <TabList class="grid grid-cols-3 gap-x-3 gap-y-4 md:gap-x-5 md:gap-y-5">
                                <Tab v-for="(image,index) in product.images" :key="image.id"
                                    class="relative flex aspect-square h-auto cursor-pointer items-center justify-center rounded-md text-gray-900 hover:bg-gray-50"
                                    v-slot="{ selected }"
                                    
                                >
                                    <span class="flex items-center absolute inset-0 overflow-hidden rounded-md ">
                                        <Image :src="image.source" alt="" class="" />
                                    </span>
                                    <div :class="[selected ? 'ring-2 ring-offset-2 ring-indigo-500' : 'ring-1 ring-gray-300', 'pointer-events-none absolute inset-0 rounded-md ']"
                                        aria-hidden="true">

                                    </div>

                                    <div v-if="!isLoading.includes(image.id)" @click.stop="deleteImage(image,index)" class="absolute top-0.5 right-0.5 py-1 px-0.5 rounded flex justify-center items-center bg-red-500/50 hover:bg-red-500 cursor-pointer text-white">
                                        <FontAwesomeIcon icon='fal fa-trash' class='text-xs' fixed-width aria-hidden='true' />
                                    </div>
                                    <div v-else class="absolute top-1.5 right-1.5 py-1 px-0.5 rounded-sm flex justify-center items-center bg-red-500/70 hover:bg-red-500 cursor-pointer text-white">
                                        <LoadingIcon />
                                    </div>
                                </Tab>

                                <div @click="isModalGallery = true"
                                    class="border border-dashed border-gray-300 relative flex aspect-square h-auto cursor-pointer items-center justify-center rounded-md hover:bg-gray-100"
                                >
                                    <FontAwesomeIcon icon='fal fa-plus' class="text-xl md:text-2xl" fixed-width aria-hidden='true' />
                                </div>
                            </TabList>
                        </div>

                    </TabGroup>
                </div>
            </div>

            <!-- Order summary -->
            <section aria-labelledby="summary-heading"
                class="border border-gray-200 rounded-lg px-4 py-6 sm:p-4 lg:mt-0 lg:p-5">
                <h2 id="summary-heading" class="text-lg font-medium">{{ trans('Product summary')}}</h2>

                <dl class="mt-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <dt class="text-sm">{{ trans('Added date') }}</dt>
                        <dd class="text-sm font-medium">{{ useFormatTime(data.product.data.created_at) }}</dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt class="text-sm">{{ trans('Stock') }}</dt>
                        <dd class="text-sm font-medium">-- pcs</dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt class="text-sm">{{ trans('Cost') }}</dt>
                        <dd class="text-sm font-medium">--</dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt class="text-sm">{{ trans('Price') }}</dt>
                        <dd class="text-sm font-medium text-right">
                            {{ locale.currencyFormat('usd', data.product.data.price) }}
                            <span class="font-light">margin (--)</span>
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt class="text-sm">RRP</dt>
                        <dd class="text-sm font-medium text-right">--- <span
                                class="font-light">margin (--)</span></dd>
                    </div>
                </dl>
            </section>
        </div>

        <!-- Revenue -->
        <div class="pt-8 p-4 col-span-3">
            <h3 class="text-base font-semibold leading-6">All sales since: Mon 20 August 2007</h3>
            <dl class="mt-5 grid grid-cols-1 overflow-hidden rounded bg-white md:grid-cols-3 md:gap-x-2 md:gap-y-4">
                <div v-for="item in stats" :key="item.name" class="px-4 py-5 sm:p-6 border border-gray-200 rounded-md">
                    <dt class="text-base font-normal">{{ item.name }}</dt>
                    <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                        <div class="flex items-baseline text-2xl font-semibold text-indigo-600">
                            {{ item.stat }}
                            <span class="ml-2 text-sm font-medium text-gray-500">from {{ item.previousStat }}</span>
                        </div>
                        <div
                            :class="[item.changeType === 'increase' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800', 'inline-flex items-baseline rounded-full px-2.5 py-0.5 text-sm font-medium md:mt-2 lg:mt-0']">
                            <!-- <ArrowUpIcon v-if="item.changeType === 'increase'"
                                class="-ml-1 mr-0.5 h-5 w-5 flex-shrink-0 self-center text-green-500"
                                aria-hidden="true" />
                            <ArrowDownIcon v-else class="-ml-1 mr-0.5 h-5 w-5 flex-shrink-0 self-center text-red-500"
                                aria-hidden="true" /> -->
                            <span class="sr-only"> {{ item.changeType === 'increase' ? 'Increased' : 'Decreased' }} by
                            </span>
                            {{ item.change }}
                        </div>
                    </dd>
                </div>
            </dl>
        </div>
        <!-- <pre>{{ data.product }}</pre> -->
    </div>


    <Modal :isOpen="isModalGallery" @onClose="() => isModalGallery = false" width="w-3/4" >
        <GalleryManagement
            :uploadRoute="route(data.uploadImageRoute.name, data.uploadImageRoute.parameters)"
            @onSuccessUpload="(data: {}) => product.images = product.images.concat(data.data)"
        >
        </GalleryManagement>
    </Modal>
</template>
