<template>
    <div class="bg-white">
        <div class="flex flex-col border-b border-gray-200 lg:border-0">
            <nav aria-label="Offers" class="order-last lg:order-first">
                <div class="mx-auto max-w-7xl lg:px-8">
                    <ul role="list" class="grid grid-cols-1 divide-y divide-gray-200 lg:grid-cols-3 lg:divide-y-0 lg:divide-x">
                        <li v-for="offer in offers" :key="offer.name" class="flex flex-col">
                            <a :href="offer.href" class="relative flex flex-1 flex-col justify-center bg-white py-6 px-4 text-center focus:z-10">
                                <p class="text-sm text-gray-500">{{ offer.name }}</p>
                                <p class="font-semibold text-gray-900">{{ offer.description }}</p>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="relative">
                <div aria-hidden="true" class="absolute hidden h-full w-1/2 bg-gray-100 lg:block" />
                <div class="relative bg-gray-100 lg:bg-transparent">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:grid lg:grid-cols-2 lg:px-8">
                        <div class="mx-auto max-w-2xl py-24 lg:max-w-none lg:py-64">
                            <div class="lg:pr-16">
                                <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl xl:text-6xl">Focus on what matters</h1>
                                <p class="mt-4 text-xl text-gray-600">All the charts, datepickers, and notifications in the world can't beat checking off some items on a paper card.</p>
                                <div class="mt-6">
                                    <a href="#" class="inline-block rounded-md border border-transparent bg-indigo-600 py-3 px-8 font-medium text-white hover:bg-indigo-700">Shop Productivity</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="h-48 w-full sm:h-64 lg:absolute lg:top-0 lg:right-0 lg:h-full lg:w-1/2">
                    <img src="https://tailwindui.com/img/ecommerce-images/home-page-02-hero-half-width.jpg" alt="" class="h-full w-full object-cover object-center" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const offers = [
    { name: 'Download the app', description: 'Get an exclusive $5 off code', href: '#' },
    { name: "Return when you're ready", description: '60 days of free returns', href: '#' },
    { name: 'Sign up for our newsletter', description: '15% off your first order', href: '#' },
]
</script>
