<script setup lang='ts'>
import Button from '@/Components/Elements/Buttons/Button.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faLongArrowAltDown } from '@fal'
import { faStar, faCircle, faHandPointer } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
library.add(faLongArrowAltDown, faStar, faCircle, faHandPointer)


const product = {
    name: 'Zip Tote Basket',
    price: '$140',
    rating: 4,
    image:
    {
        id: 1,
        name: 'Angled view',
        src: 'https://tailwindui.com/plus/img/ecommerce-images/product-page-01-product-shot-02.jpg',
        alt: 'Angled front view with bag zipped and handles upright.',
    },

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
}
</script>

<template>
    <div class="border-2 p-4 rounded-lg">
        <div class="bg-white">
            <div>
                <!-- Product image -->
                <div class="mt-4 flex justify-center">
                    <img :src="product.image.src" alt="Product Image" class="max-w-full h-auto rounded-md">
                </div>
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
                                <StarIcon v-for="rating in [0, 1, 2, 3, 4]" :key="rating"
                                    :class="[product.rating > rating ? 'text-indigo-500' : 'text-gray-300', 'h-5 w-5 flex-shrink-0']"
                                    aria-hidden="true" />
                            </div>
                            <p class="sr-only">{{ product.rating }} out of 5 stars</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="sr-only">Description</h3>

                        <div class="space-y-6 text-base text-gray-700" v-html="product.description" />
                    </div>

                    <form class="mt-6">
                        <div class="mt-10 flex">
                            <button type="submit"
                                class="flex max-w-xs flex-1 items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50 sm:w-full">Add
                                to bag</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</template>