<script setup lang="ts">
import { ref } from "vue";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faStar, faDollarSign, faGlobe } from "@fal";
import { TabGroup, TabList, Tab, TabPanels, TabPanel } from "@headlessui/vue";
import { RadioGroup, RadioGroupOption } from "@headlessui/vue";
import { getStyles } from "@/Composables/styles";
import Image from "@/Components/Image.vue";

library.add(faStar, faDollarSign, faGlobe);

const product = {
    name: 'Zip Tote Basket',
    price: '$140',
    rating: 4,
    images: [
        {
            id: 1,
            name: 'Angled view',
            src: 'https://tailwindui.com/plus/img/ecommerce-images/product-page-03-product-01.jpg',
            alt: 'Angled front view with bag zipped and handles upright.',
        },
        {
            id: 2,
            name: 'Side view',
            src: 'https://tailwindui.com/plus/img/ecommerce-images/product-page-03-product-02.jpg',
            alt: 'Side view of the bag showing the handles and zipper.',
        },
    ],
    colors: [
        { name: 'Washed Black', bgColor: 'bg-gray-700', selectedColor: 'ring-gray-700' },
        { name: 'White', bgColor: 'bg-white', selectedColor: 'ring-gray-400' },
        { name: 'Washed Gray', bgColor: 'bg-gray-500', selectedColor: 'ring-gray-500' },
    ],
    description: `
      <p>The Zip Tote Basket is the perfect midpoint between shopping tote and comfy backpack. With convertible straps, you can hand carry, should sling, or backpack this convenient and spacious bag. The zip top and durable canvas construction keeps your goods protected for all-day use.</p>
    `,
    details: [
        {
            name: 'Features',
            items: [
                'Multiple strap configurations',
                'Spacious interior with top zip',
                'Leather handle and tabs',
                'Interior dividers',
                'Stainless strap loops',
                'Double stitched construction',
                'Water-resistant',
            ],
        },
    ],
};

const selectedColor = ref(product.colors[0]);
</script>

<template>
    <div class="bg-white">
        <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-x-8">
                <!-- Image gallery -->
                <TabGroup as="div" class="flex flex-col-reverse">
                    <!-- Image selector -->
                    <div class="mx-auto mt-6 hidden w-full max-w-2xl sm:block lg:max-w-none">
                        <TabList class="grid grid-cols-4 gap-6">
                            <Tab v-for="image in product.images" :key="image.id" class="relative cursor-pointer">
                                <span class="sr-only">{{ image.name }}</span>
                                <span class="absolute inset-0 overflow-hidden rounded-md">
                                    <img :src="image.src" alt="" class="h-full w-full object-cover object-center" />
                                </span>
                                <span
                                    class="ring-indigo-500 pointer-events-none absolute inset-0 rounded-md ring-2 ring-offset-2"
                                    aria-hidden="true" />
                            </Tab>
                        </TabList>
                    </div>

                    <!-- Large image display -->
                    <TabPanels>
                        <TabPanel v-for="image in product.images" :key="image.id">
                            <img :src="image.src" :alt="image.alt"
                                class="h-full w-full object-cover object-center sm:rounded-lg" />
                        </TabPanel>
                    </TabPanels>
                </TabGroup>

                <!-- Product info -->
                <div class="mt-10 px-4 sm:mt-16 sm:px-0 lg:mt-0">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ product.name }}</h1>

                    <div class="mt-3">
                        <h2 class="sr-only">Product information</h2>
                        <p class="text-3xl tracking-tight text-gray-900">{{ product.price }}</p>
                    </div>

                    <!-- Reviews -->
                    <div class="mt-3">
                        <h3 class="sr-only">Reviews</h3>
                        <div class="flex items-center">
                            <div class="flex items-center">
                                <svg v-for="rating in [0, 1, 2, 3, 4]" :key="rating"
                                    :class="product.rating > rating ? 'text-indigo-500' : 'text-gray-300'"
                                    class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"
                                    aria-hidden="true">
                                    <path
                                        d="M9.049.667a1.36 1.36 0 011.902 0l2.617 5.304 5.857.852c.45.065.63.62.304.93l-4.236 4.131.999 5.821a.64.64 0 01-.928.676L10 15.944l-5.238 2.753a.64.64 0 01-.928-.676l.999-5.82-4.236-4.132a.64.64 0 01.304-.93l5.857-.852L9.049.667z" />
                                </svg>
                            </div>
                            <p class="sr-only">{{ product.rating }} out of 5 stars</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="sr-only">Description</h3>

                        <div class="space-y-6 text-base text-gray-700" v-html="product.description" />
                    </div>

                    <form class="mt-6">
                        <!-- Colors -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-600">Color</h3>

                            <fieldset aria-label="Choose a color" class="mt-2">
                                <RadioGroup v-model="selectedColor" class="flex items-center space-x-3">
                                    <RadioGroupOption as="template" v-for="color in product.colors" :key="color.name"
                                        :value="color" :aria-label="color.name" v-slot="{ active, checked }">
                                        <div
                                            :class="[color.selectedColor, active && checked ? 'ring ring-offset-1' : '', !active && checked ? 'ring-2' : '', 'relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none']">
                                            <span aria-hidden="true"
                                                :class="[color.bgColor, 'h-8 w-8 rounded-full border border-black border-opacity-10']" />
                                        </div>
                                    </RadioGroupOption>
                                </RadioGroup>
                            </fieldset>
                        </div>

                        <div class="mt-10 flex">
                            <button type="submit"
                                class="flex max-w-xs flex-1 items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50 sm:w-full">Add
                                to bag</button>

                            <button type="button"
                                class="ml-4 flex items-center justify-center rounded-md px-3 py-3 text-gray-400 hover:bg-gray-100 hover:text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24"
                                    stroke="currentColor" fill="none">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5.121 19.121a1.5 1.5 0 010-2.121l12-12a1.5 1.5 0 112.121 2.121l-12 12a1.5 1.5 0 01-2.121 0z" />
                                </svg>
                                <span class="sr-only">Add to favorites</span>
                            </button>
                        </div>
                    </form>

                    <section aria-labelledby="details-heading" class="mt-12">
                        <h2 id="details-heading" class="sr-only">Additional details</h2>

                        <div class="divide-y divide-gray-200 border-t">
                            <Disclosure as="div" v-for="detail in product.details" :key="detail.name">
                                <DisclosureButton as="h3" class="py-6 text-sm font-medium">
                                    {{ detail.name }}
                                </DisclosureButton>
                                <DisclosurePanel as="div" class="prose prose-sm pb-6">
                                    <ul role="list">
                                        <li v-for="item in detail.items" :key="item">{{ item }}</li>
                                    </ul>
                                </DisclosurePanel>
                            </Disclosure>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>
