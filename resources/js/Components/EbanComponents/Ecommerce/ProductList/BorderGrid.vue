<template>
    <div class="bg-white">
        <div class="mx-auto max-w-7xl overflow-hidden sm:px-6 lg:px-8">
            <h2 class="sr-only">Products</h2>

            <div class="-mx-px grid grid-cols-2 border-l border-gray-200 sm:mx-0 md:grid-cols-3 lg:grid-cols-4">
                <div v-for="product in products" :key="product.id" class="group relative border-r border-b border-gray-200 p-4 sm:p-6">
                    <div class="aspect-w-1 aspect-h-1 overflow-hidden rounded-lg bg-gray-200 group-hover:opacity-75">
                        <img :src="product.imageSrc" :alt="product.imageAlt" class="h-full w-full object-cover object-center" />
                    </div>
                    <div class="pt-10 pb-4 text-center">
                        <h3 class="text-sm font-medium text-gray-900">
                            <a :href="product.href">
                                <span aria-hidden="true" class="absolute inset-0" />
                                {{ product.name }}
                            </a>
                        </h3>
                        <div class="mt-3 flex flex-col items-center">
                            <p class="sr-only">{{ product.rating }} out of 5 stars</p>
                            <div class="flex items-center">
                                <StarIcon v-for="rating in [0, 1, 2, 3, 4]" :key="rating" :class="[product.rating > rating ? 'text-yellow-400' : 'text-gray-200', 'flex-shrink-0 h-5 w-5']" aria-hidden="true" />
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ product.reviewCount }} reviews</p>
                        </div>
                        <p class="mt-4 text-base font-medium text-gray-900">{{ product.price }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { StarIcon } from '@heroicons/vue/20/solid'

const products = [
    {
        id: 1,
        name: 'Organize Basic Set (Walnut)',
        price: '$149',
        rating: 5,
        reviewCount: 38,
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/category-page-05-image-card-01.jpg',
        imageAlt: 'TODO',
        href: '#',
    },
    {
        id: 2,
        name: 'Organize Pen Holder',
        price: '$15',
        rating: 5,
        reviewCount: 18,
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/category-page-05-image-card-02.jpg',
        imageAlt: 'TODO',
        href: '#',
    },
    {
        id: 3,
        name: 'Organize Sticky Note Holder',
        price: '$15',
        rating: 5,
        reviewCount: 14,
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/category-page-05-image-card-03.jpg',
        imageAlt: 'TODO',
        href: '#',
    },
    {
        id: 4,
        name: 'Organize Phone Holder',
        price: '$15',
        rating: 4,
        reviewCount: 21,
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/category-page-05-image-card-04.jpg',
        imageAlt: 'TODO',
        href: '#',
    },
    // More products...
]
</script>
