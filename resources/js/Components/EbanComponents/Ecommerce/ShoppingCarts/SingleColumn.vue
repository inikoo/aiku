<template>
    <div class="bg-white">
        <div class="mx-auto max-w-2xl py-16 px-4 sm:py-24 sm:px-6 lg:px-0">
            <h1 class="text-center text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Shopping Cart</h1>

            <form class="mt-12">
                <section aria-labelledby="cart-heading">
                    <h2 id="cart-heading" class="sr-only">Items in your shopping cart</h2>

                    <ul role="list" class="divide-y divide-gray-200 border-t border-b border-gray-200">
                        <li v-for="product in products" :key="product.id" class="flex py-6">
                            <div class="flex-shrink-0">
                                <img :src="product.imageSrc" :alt="product.imageAlt" class="h-24 w-24 rounded-md object-cover object-center sm:h-32 sm:w-32" />
                            </div>

                            <div class="ml-4 flex flex-1 flex-col sm:ml-6">
                                <div>
                                    <div class="flex justify-between">
                                        <h4 class="text-sm">
                                            <a :href="product.href" class="font-medium text-gray-700 hover:text-gray-800">{{ product.name }}</a>
                                        </h4>
                                        <p class="ml-4 text-sm font-medium text-gray-900">{{ product.price }}</p>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">{{ product.color }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ product.size }}</p>
                                </div>

                                <div class="mt-4 flex flex-1 items-end justify-between">
                                    <p class="flex items-center space-x-2 text-sm text-gray-700">
                                        <CheckIcon v-if="product.inStock" class="h-5 w-5 flex-shrink-0 text-green-500" aria-hidden="true" />
                                        <ClockIcon v-else class="h-5 w-5 flex-shrink-0 text-gray-300" aria-hidden="true" />
                                        <span>{{ product.inStock ? 'In stock' : `Will ship in ${product.leadTime}` }}</span>
                                    </p>
                                    <div class="ml-4">
                                        <button type="button" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                                            <span>Remove</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </section>

                <!-- Order summary -->
                <section aria-labelledby="summary-heading" class="mt-10">
                    <h2 id="summary-heading" class="sr-only">Order summary</h2>

                    <div>
                        <dl class="space-y-4">
                            <div class="flex items-center justify-between">
                                <dt class="text-base font-medium text-gray-900">Subtotal</dt>
                                <dd class="ml-4 text-base font-medium text-gray-900">$96.00</dd>
                            </div>
                        </dl>
                        <p class="mt-1 text-sm text-gray-500">Shipping and taxes will be calculated at checkout.</p>
                    </div>

                    <div class="mt-10">
                        <button type="submit" class="w-full rounded-md border border-transparent bg-indigo-600 py-3 px-4 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Checkout</button>
                    </div>

                    <div class="mt-6 text-center text-sm">
                        <p>
                            or
                            <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
                                Continue Shopping
                                <span aria-hidden="true"> &rarr;</span>
                            </a>
                        </p>
                    </div>
                </section>
            </form>
        </div>
    </div>
</template>

<script setup>
import { CheckIcon, ClockIcon } from '@heroicons/vue/20/solid'

const products = [
    {
        id: 1,
        name: 'Artwork Tee',
        href: '#',
        price: '$32.00',
        color: 'Mint',
        size: 'Medium',
        inStock: true,
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/checkout-page-03-product-04.jpg',
        imageAlt: 'Front side of mint cotton t-shirt with wavey lines pattern.',
    },
    {
        id: 2,
        name: 'Basic Tee',
        href: '#',
        price: '$32.00',
        color: 'Charcoal',
        inStock: false,
        leadTime: '7-8 years',
        size: 'Large',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/shopping-cart-page-01-product-02.jpg',
        imageAlt: 'Front side of charcoal cotton t-shirt.',
    },
    // More products...
]
</script>
