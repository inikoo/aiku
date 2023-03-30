<template>
    <div class="bg-white">
        <div class="mx-auto max-w-2xl py-16 px-4 sm:py-24 sm:px-6 lg:grid lg:max-w-7xl lg:grid-cols-2 lg:gap-x-8 lg:px-8">
            <!-- Product details -->
            <div class="lg:max-w-lg lg:self-end">
                <nav aria-label="Breadcrumb">
                    <ol role="list" class="flex items-center space-x-2">
                        <li v-for="(breadcrumb, breadcrumbIdx) in product.breadcrumbs" :key="breadcrumb.id">
                            <div class="flex items-center text-sm">
                                <a :href="breadcrumb.href" class="font-medium text-gray-500 hover:text-gray-900">{{ breadcrumb.name }}</a>
                                <svg v-if="breadcrumbIdx !== product.breadcrumbs.length - 1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="ml-2 h-5 w-5 flex-shrink-0 text-gray-300">
                                    <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                                </svg>
                            </div>
                        </li>
                    </ol>
                </nav>

                <div class="mt-4">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ product.name }}</h1>
                </div>

                <section aria-labelledby="information-heading" class="mt-4">
                    <h2 id="information-heading" class="sr-only">Product information</h2>

                    <div class="flex items-center">
                        <p class="text-lg text-gray-900 sm:text-xl">{{ product.price }}</p>

                        <div class="ml-4 border-l border-gray-300 pl-4">
                            <h2 class="sr-only">Reviews</h2>
                            <div class="flex items-center">
                                <div>
                                    <div class="flex items-center">
                                        <StarIcon v-for="rating in [0, 1, 2, 3, 4]" :key="rating" :class="[reviews.average > rating ? 'text-yellow-400' : 'text-gray-300', 'h-5 w-5 flex-shrink-0']" aria-hidden="true" />
                                    </div>
                                    <p class="sr-only">{{ reviews.average }} out of 5 stars</p>
                                </div>
                                <p class="ml-2 text-sm text-gray-500">{{ reviews.totalCount }} reviews</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 space-y-6">
                        <p class="text-base text-gray-500">{{ product.description }}</p>
                    </div>

                    <div class="mt-6 flex items-center">
                        <CheckIcon class="h-5 w-5 flex-shrink-0 text-green-500" aria-hidden="true" />
                        <p class="ml-2 text-sm text-gray-500">In stock and ready to ship</p>
                    </div>
                </section>
            </div>

            <!-- Product image -->
            <div class="mt-10 lg:col-start-2 lg:row-span-2 lg:mt-0 lg:self-center">
                <div class="aspect-w-1 aspect-h-1 overflow-hidden rounded-lg">
                    <img :src="product.imageSrc" :alt="product.imageAlt" class="h-full w-full object-cover object-center" />
                </div>
            </div>

            <!-- Product form -->
            <div class="mt-10 lg:col-start-1 lg:row-start-2 lg:max-w-lg lg:self-start">
                <section aria-labelledby="options-heading">
                    <h2 id="options-heading" class="sr-only">Product options</h2>

                    <form>
                        <div class="sm:flex sm:justify-between">
                            <!-- Size selector -->
                            <RadioGroup v-model="selectedSize">
                                <RadioGroupLabel class="block text-sm font-medium text-gray-700">Size</RadioGroupLabel>
                                <div class="mt-1 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <RadioGroupOption as="template" v-for="size in product.sizes" :key="size.name" :value="size" v-slot="{ active, checked }">
                                        <div :class="[active ? 'ring-2 ring-indigo-500' : '', 'relative block cursor-pointer rounded-lg border border-gray-300 p-4 focus:outline-none']">
                                            <RadioGroupLabel as="p" class="text-base font-medium text-gray-900">{{ size.name }}</RadioGroupLabel>
                                            <RadioGroupDescription as="p" class="mt-1 text-sm text-gray-500">{{ size.description }}</RadioGroupDescription>
                                            <div :class="[active ? 'border' : 'border-2', checked ? 'border-indigo-500' : 'border-transparent', 'pointer-events-none absolute -inset-px rounded-lg']" aria-hidden="true" />
                                        </div>
                                    </RadioGroupOption>
                                </div>
                            </RadioGroup>
                        </div>
                        <div class="mt-4">
                            <a href="#" class="group inline-flex text-sm text-gray-500 hover:text-gray-700">
                                <span>What size should I buy?</span>
                                <QuestionMarkCircleIcon class="ml-2 h-5 w-5 flex-shrink-0 text-gray-400 group-hover:text-gray-500" aria-hidden="true" />
                            </a>
                        </div>
                        <div class="mt-10">
                            <button type="submit" class="flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 py-3 px-8 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50">Add to bag</button>
                        </div>
                        <div class="mt-6 text-center">
                            <a href="#" class="group inline-flex text-base font-medium">
                                <ShieldCheckIcon class="mr-2 h-6 w-6 flex-shrink-0 text-gray-400 group-hover:text-gray-500" aria-hidden="true" />
                                <span class="text-gray-500 hover:text-gray-700">Lifetime Guarantee</span>
                            </a>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { CheckIcon, QuestionMarkCircleIcon, StarIcon } from '@heroicons/vue/20/solid'
import { RadioGroup, RadioGroupDescription, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import { ShieldCheckIcon } from '@heroicons/vue/24/outline'

const product = {
    name: 'Everyday Ruck Snack',
    href: '#',
    price: '$220',
    description:
        "Don't compromise on snack-carrying capacity with this lightweight and spacious bag. The drawstring top keeps all your favorite chips, crisps, fries, biscuits, crackers, and cookies secure.",
    imageSrc: 'https://tailwindui.com/img/ecommerce-images/product-page-04-featured-product-shot.jpg',
    imageAlt: 'Model wearing light green backpack with black canvas straps and front zipper pouch.',
    breadcrumbs: [
        { id: 1, name: 'Travel', href: '#' },
        { id: 2, name: 'Bags', href: '#' },
    ],
    sizes: [
        { name: '18L', description: 'Perfect for a reasonable amount of snacks.' },
        { name: '20L', description: 'Enough room for a serious amount of snacks.' },
    ],
}
const reviews = { average: 4, totalCount: 1624 }

const selectedSize = ref(product.sizes[0])
</script>
