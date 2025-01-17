<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref } from "vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faStar, faDollarSign, faGlobe } from "@fal";
import { RadioGroup, RadioGroupOption } from "@headlessui/vue";
import { getStyles } from "@/Composables/styles";
import Image from "@/Components/Image.vue";
import { WebBlockParameters } from '../../../types/webpageTypes';

library.add(faStar, faDollarSign, faGlobe);

const props = defineProps<{
    properties: {};
    modelValue: any;
    webpageData?: any;
    web_block: Object
}>();

// Parse product data from modelValue
const productLayout = ref(props.web_block);
const web_blocks_parameters = ref(props.webpageData?.web_blocks_parameters?.data)
if (web_blocks_parameters.value) {
    const product = ref(web_blocks_parameters.value.find(x => x.id === productLayout?.value.id))
}
</script>

<template>

    <div class="bg-white" :style="getStyles(properties)">
        <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-x-8">
                <!-- Image gallery -->
                <TabGroup as="div" class="flex flex-col-reverse">
                    <!-- Image selector -->
                    <div class="mx-auto mt-6 hidden w-full max-w-2xl sm:block lg:max-w-none">
                        <TabList class="grid grid-cols-4 gap-6">
                            <Tab v-if="product?.products[0].images && product?.products[0].images.length"
                                v-for="image in product?.products[0].images"
                                class="relative flex h-24 cursor-pointer items-center justify-center rounded-md bg-white text-sm font-medium uppercase text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring focus:ring-opacity-50 focus:ring-offset-4">
                                <span class="sr-only">{{ image?.name }}</span>
                                <span class="absolute inset-0 overflow-hidden rounded-md">
                                    <Image :src="image?.source" alt="Informative Image"
                                        class="w-full h-full object-cover rounded-md" />
                                </span>
                                <span
                                    :class="[selected ? 'ring-indigo-500' : 'ring-transparent', 'pointer-events-none absolute inset-0 rounded-md ring-2 ring-offset-2']"
                                    aria-hidden="true" />
                            </Tab>

                            <Tab v-else
                                class="relative flex h-24 cursor-pointer items-center justify-center rounded-md bg-white text-sm font-medium uppercase text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring focus:ring-opacity-50 focus:ring-offset-4">
                                <span class="sr-only">Default Image</span>
                                <span class="absolute inset-0 overflow-hidden rounded-md">
                                    <img src="https://tailwindui.com/plus/img/ecommerce-images/product-page-03-product-01.jpg"
                                        alt="Default Image" class="w-full h-full object-cover rounded-md" />
                                </span>
                            </Tab>
                        </TabList>
                    </div>

                    <TabPanels class="aspect-h-1 aspect-w-1 w-full">
                        <TabPanel v-if="product?.products[0].images && product?.products[0].images.length"
                            v-for="image in product?.products[0].images" :key="image.id">
                            <Image
                                :src="image?.source || 'https://tailwindui.com/plus/img/ecommerce-images/product-page-03-product-01.jpg'"
                                :alt="image?.name || 'Default alt text'"
                                class="h-full w-full object-cover object-center sm:rounded-lg" />
                        </TabPanel>

                        <TabPanel v-else>
                            <img src="https://tailwindui.com/plus/img/ecommerce-images/product-page-03-product-01.jpg"
                                alt="Default Image" class="h-full w-full object-cover object-center sm:rounded-lg" />
                        </TabPanel>
                    </TabPanels>
                </TabGroup>

                <!-- Product info -->
                <div class="mt-10 px-4 sm:mt-16 sm:px-0 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                        {{ product?.products[0].name || 'Default Product Name' }}
                    </h1>

                    <div class="mt-3">
                        <h2 class="sr-only">Product information</h2>
                        <p class="text-3xl tracking-tight text-gray-900">
                            {{ product?.products[0].price || 'Price not available' }}
                        </p>
                    </div>

                    <div class="mt-6">
                        <h3 class="sr-only">Description</h3>
                        <div class="space-y-6 text-base text-gray-700"
                            v-html="productLayout?.value?.text || 'No description available.'" />
                    </div>

                    <form class="mt-6">
                        <div class="mt-10 flex">
                            <button type="submit"
                                class="flex max-w-xs flex-1 items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50 sm:w-full">
                                Add to bag
                            </button>

                            <button type="button"
                                class="ml-4 flex items-center justify-center rounded-md px-3 py-3 text-gray-400 hover:bg-gray-100 hover:text-gray-500">
                                <HeartIcon class="h-6 w-6 flex-shrink-0" aria-hidden="true" />
                                <span class="sr-only">Add to favorites</span>
                            </button>
                        </div>
                    </form>

                    <section aria-labelledby="details-heading" class="mt-12">
                        <h2 id="details-heading" class="sr-only">Additional details</h2>
                        <div class="divide-y divide-gray-200 border-t">
                            <Disclosure as="div" v-if="product?.details && product.details.length"
                                v-for="detail in product.details" :key="detail.name">
                                <h3>
                                    <DisclosureButton
                                        class="group relative flex w-full items-center justify-between py-6 text-left">
                                        <span
                                            :class="[open ? 'text-indigo-600' : 'text-gray-900', 'text-sm font-medium']">{{
                                                detail.name }}</span>
                                        <span class="ml-6 flex items-center">
                                            <PlusIcon v-if="!open"
                                                class="block h-6 w-6 text-gray-400 group-hover:text-gray-500"
                                                aria-hidden="true" />
                                            <MinusIcon v-else
                                                class="block h-6 w-6 text-indigo-400 group-hover:text-indigo-500"
                                                aria-hidden="true" />
                                        </span>
                                    </DisclosureButton>
                                </h3>
                                <DisclosurePanel as="div" class="prose prose-sm pb-6">
                                    <ul role="list">
                                        <li v-for="item in detail.items" :key="item">{{ item }}</li>
                                    </ul>
                                </DisclosurePanel>
                            </Disclosure>
                            <div v-else class="py-6 text-gray-700">
                                No additional details available.
                            </div>
                        </div>
                    </section>
                </div>

            </div>
        </div>
    </div>
</template>
