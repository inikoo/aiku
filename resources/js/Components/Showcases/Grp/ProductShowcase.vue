<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 04 Apr 2023 11:19:33 Malaysia Time, Sanur, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Gallery from "@/Components/Fulfilment/Website/Gallery/Gallery.vue";
import { useLocaleStore } from '@/Stores/locale'
import { faCircle, faTrash } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { notify } from '@kyvg/vue3-notification'
import Image from "@/Components/Image.vue";
import {
    Tab,
    TabGroup,
    TabList,
    TabPanel,
    TabPanels,
} from '@headlessui/vue'
import { ref } from 'vue'
import EmptyState from "@/Components/Utils/EmptyState.vue";
import axios from "axios";
library.add(faCircle, faTrash)

const props = defineProps<{
    data: Object
}>()


const locale = useLocaleStore()
const openGallery = ref(false)
const selectedImage = ref(0)


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

const OnUploadImages = (e) => {
    product.value.images.push(...e.data)
    openGallery.value = false
}

const OnPickImages = (e) => {
    product.value.images.push(e)
    openGallery.value = false
}

const deleteImage = async (data,index) => {
    console.log(data)

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
    }
}


function changeSelectedImage(index) {
    selectedImage.value = index
}


</script>


<template>
    <div class="grid grid-cols-4 gap-x-1 gap-y-4">
        <div class="p-5 space-y-5">
            <div class="relative">
                <div class=" h-full aspect-square rounded-lg shadow">
                    <TabGroup as="div" class="flex flex-col-reverse p-2.5" :selectedIndex="selectedImage" @change="changeSelectedImage">
                        <div class="mx-auto mt-6 hidden w-full max-w-2xl sm:block lg:max-w-none">
                            <TabList class="grid grid-cols-3 gap-6">
                                <Tab v-for="(image,index) in product.images" :key="image.id"
                                    class="relative flex h-24 w-full cursor-pointer items-center justify-center rounded-md bg-white text-sm font-medium uppercase text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring focus:ring-opacity-50 focus:ring-offset-4"
                                    v-slot="{ selected }">
                                    <span class="sr-only">{{ image.name }}</span>
                                    <span class="absolute inset-0 overflow-hidden rounded-md ">
                                        <Image :src="image.source" alt=""
                                            class="h-full w-full object-cover object-center" />
                                    </span>
                                    <div :class="[selected ? 'ring-indigo-500' : 'ring-transparent', 'pointer-events-none absolute inset-0 rounded-md ring-2 ring-offset-2']"
                                        aria-hidden="true">

                                    </div>
                                    <font-awesome-icon :icon="['fas', 'trash']"
                                            class="absolute top-2 right-2 text-red-400 cursor-pointer"
                                            @click.stop="deleteImage(image,index)" />
                                </Tab>
                            </TabList>

                        </div>

                        <TabPanels class="overflow-hidden duration-300">
                            <!-- Menggunakan v-if pada elemen utama untuk kondisi gambar ada -->
                            <template v-if="product.images.length > 0">
                                <TabPanel v-for="image in product.images" :key="image.id">
                                    <div
                                        class="border-2 border-gray-200 rounded-lg shadow-md hover:shadow-lg transition-shadow aspect-[1/1] w-full h-[300px] relative overflow-hidden">
                                        <Image :src="image.source" :alt="image.name" @click="openGallery = true"
                                            class="w-full h-full object-cover object-center" />
                                    </div>
                                </TabPanel>
                            </template>

                            <!-- Menggunakan template v-else untuk kondisi gambar tidak ada -->
                            <template v-else>
                                <TabPanel>
                                    <EmptyState
                                        :data="{ title: 'You don\'t have any images', description: 'Click to upload' }"
                                        @click="openGallery = true" />
                                </TabPanel>
                            </template>
                        </TabPanels>

                    </TabGroup>
                </div>
            </div>

            <!-- Order summary -->
            <section aria-labelledby="summary-heading"
                class="border border-gray-200 rounded-lg px-4 py-6 sm:p-4 lg:mt-0 lg:p-5">
                <h2 id="summary-heading" class="text-lg font-medium">Product summary</h2>

                <dl class="mt-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Added date</dt>
                        <dd class="text-sm font-medium">{{ product.created_at }}</dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Stock</dt>
                        <dd class="text-sm font-medium">24 pcs</dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Cost</dt>
                        <dd class="text-sm font-medium">{{ locale.currencyFormat('usd', 8.80) }}</dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Price</dt>
                        <dd class="text-sm font-medium text-right">{{ locale.currencyFormat('usd', product.price) }}
                            <span class="font-light">margin (45.0%)</span>
                        </dd>
                    </div>

                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">RRP</dt>
                        <dd class="text-sm font-medium text-right">{{ locale.currencyFormat('usd', 2.95) }} <span
                                class="font-light">margin (66.1%)</span></dd>
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
                            <ArrowUpIcon v-if="item.changeType === 'increase'"
                                class="-ml-1 mr-0.5 h-5 w-5 flex-shrink-0 self-center text-green-500"
                                aria-hidden="true" />
                            <ArrowDownIcon v-else class="-ml-1 mr-0.5 h-5 w-5 flex-shrink-0 self-center text-red-500"
                                aria-hidden="true" />
                            <span class="sr-only"> {{ item.changeType === 'increase' ? 'Increased' : 'Decreased' }} by
                            </span>
                            {{ item.change }}
                        </div>
                    </dd>
                </div>
            </dl>
        </div>
    </div>


    <Gallery :open="openGallery" @on-close="openGallery = false"
        :uploadRoutes="route(data.uploadImageRoute.name, data.uploadImageRoute.parameters)" @on-upload="OnUploadImages"
        @on-pick="OnPickImages">
    </Gallery>
</template>
