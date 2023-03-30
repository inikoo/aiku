<template>
    <div class="bg-white">
        <div class="mx-auto max-w-4xl py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Shopping Cart</h1>

            <form class="mt-12">
                <div>
                    <h2 class="sr-only">Items in your shopping cart</h2>

                    <ul role="list" class="divide-y divide-gray-200 border-t border-b border-gray-200">
                        <li v-for="(product, productIdx) in products" :key="product.id" class="flex py-6 sm:py-10">
                            <div class="flex-shrink-0">
                                <img :src="product.imageSrc" :alt="product.imageAlt" class="h-24 w-24 rounded-lg object-cover object-center sm:h-32 sm:w-32" />
                            </div>

                            <div class="relative ml-4 flex flex-1 flex-col justify-between sm:ml-6">
                                <div>
                                    <div class="flex justify-between sm:grid sm:grid-cols-2">
                                        <div class="pr-6">
                                            <h3 class="text-sm">
                                                <a :href="product.href" class="font-medium text-gray-700 hover:text-gray-800">{{ product.name }}</a>
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ product.color }}</p>
                                            <p v-if="product.size" class="mt-1 text-sm text-gray-500">{{ product.size }}</p>
                                        </div>

                                        <p class="text-right text-sm font-medium text-gray-900">{{ product.price }}</p>
                                    </div>

                                    <div class="mt-4 flex items-center sm:absolute sm:top-0 sm:left-1/2 sm:mt-0 sm:block">
                                        <label :for="`quantity-${productIdx}`" class="sr-only">Quantity, {{ product.name }}</label>
                                        <select :id="`quantity-${productIdx}`" :name="`quantity-${productIdx}`" class="block max-w-full rounded-md border border-gray-300 py-1.5 text-left text-base font-medium leading-5 text-gray-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                        </select>

                                        <button type="button" class="ml-4 text-sm font-medium text-indigo-600 hover:text-indigo-500 sm:ml-0 sm:mt-3">
                                            <span>Remove</span>
                                        </button>
                                    </div>
                                </div>

                                <p class="mt-4 flex space-x-2 text-sm text-gray-700">
                                    <CheckIcon v-if="product.inStock" class="h-5 w-5 flex-shrink-0 text-green-500" aria-hidden="true" />
                                    <ClockIcon v-else class="h-5 w-5 flex-shrink-0 text-gray-300" aria-hidden="true" />
                                    <span>{{ product.inStock ? 'In stock' : `Ships in ${product.leadTime}` }}</span>
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Order summary -->
                <div class="mt-10 sm:ml-32 sm:pl-6">
                    <div class="rounded-lg bg-gray-50 px-4 py-6 sm:p-6 lg:p-8">
                        <h2 class="sr-only">Order summary</h2>

                        <div class="flow-root">
                            <dl class="-my-4 divide-y divide-gray-200 text-sm">
                                <div class="flex items-center justify-between py-4">
                                    <dt class="text-gray-600">Subtotal</dt>
                                    <dd class="font-medium text-gray-900">$99.00</dd>
                                </div>
                                <div class="flex items-center justify-between py-4">
                                    <dt class="text-gray-600">Shipping</dt>
                                    <dd class="font-medium text-gray-900">$5.00</dd>
                                </div>
                                <div class="flex items-center justify-between py-4">
                                    <dt class="text-gray-600">Tax</dt>
                                    <dd class="font-medium text-gray-900">$8.32</dd>
                                </div>
                                <div class="flex items-center justify-between py-4">
                                    <dt class="text-base font-medium text-gray-900">Order total</dt>
                                    <dd class="text-base font-medium text-gray-900">$112.32</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-10">
                        <button type="submit" class="w-full rounded-md border border-transparent bg-indigo-600 py-3 px-4 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Checkout</button>
                    </div>

                    <div class="mt-6 text-center text-sm text-gray-500">
                        <p>
                            or
                            <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
                                Continue Shopping
                                <span aria-hidden="true"> &rarr;</span>
                            </a>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { CheckIcon, ClockIcon } from '@heroicons/vue/20/solid'

const products = [
    {
        id: 1,
        name: 'Nomad Tumbler',
        href: '#',
        price: '$35.00',
        color: 'White',
        inStock: true,
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/shopping-cart-page-01-product-03.jpg',
        imageAlt: 'Insulated bottle with white base and black snap lid.',
    },
    {
        id: 2,
        name: 'Basic Tee',
        href: '#',
        price: '$32.00',
        color: 'Sienna',
        inStock: true,
        size: 'Large',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/shopping-cart-page-01-product-01.jpg',
        imageAlt: "Front of men's Basic Tee in sienna.",
    },
    // More products...
]
</script>
