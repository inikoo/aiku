<template>
    <div class="relative bg-white">
        <!-- Background image and overlap -->
        <div aria-hidden="true" class="absolute inset-0 hidden sm:flex sm:flex-col">
            <div class="relative w-full flex-1 bg-gray-800">
                <div class="absolute inset-0 overflow-hidden">
                    <img src="https://tailwindui.com/img/ecommerce-images/home-page-04-hero-full-width.jpg" alt="" class="h-full w-full object-cover object-center" />
                </div>
                <div class="absolute inset-0 bg-gray-900 opacity-50" />
            </div>
            <div class="h-32 w-full bg-white md:h-40 lg:h-48" />
        </div>

        <div class="relative mx-auto max-w-3xl px-4 pb-96 text-center sm:px-6 sm:pb-0 lg:px-8">
            <!-- Background image and overlap -->
            <div aria-hidden="true" class="absolute inset-0 flex flex-col sm:hidden">
                <div class="relative w-full flex-1 bg-gray-800">
                    <div class="absolute inset-0 overflow-hidden">
                        <img src="https://tailwindui.com/img/ecommerce-images/home-page-04-hero-full-width.jpg" alt="" class="h-full w-full object-cover object-center" />
                    </div>
                    <div class="absolute inset-0 bg-gray-900 opacity-50" />
                </div>
                <div class="h-48 w-full bg-white" />
            </div>
            <div class="relative py-32">
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl md:text-6xl">Mid-Season Sale</h1>
                <div class="mt-4 sm:mt-6">
                    <a href="#" class="inline-block rounded-md border border-transparent bg-indigo-600 py-3 px-8 font-medium text-white hover:bg-indigo-700">Shop Collection</a>
                </div>
            </div>
        </div>

        <section aria-labelledby="collection-heading" class="relative -mt-96 sm:mt-0">
            <h2 id="collection-heading" class="sr-only">Collections</h2>
            <div class="mx-auto grid max-w-md grid-cols-1 gap-y-6 px-4 sm:max-w-7xl sm:grid-cols-3 sm:gap-y-0 sm:gap-x-6 sm:px-6 lg:gap-x-8 lg:px-8">
                <div v-for="collection in collections" :key="collection.name" class="group relative h-96 rounded-lg bg-white shadow-xl sm:aspect-w-4 sm:aspect-h-5 sm:h-auto">
                    <div>
                        <div aria-hidden="true" class="absolute inset-0 overflow-hidden rounded-lg">
                            <div class="absolute inset-0 overflow-hidden group-hover:opacity-75">
                                <img :src="collection.imageSrc" :alt="collection.imageAlt" class="h-full w-full object-cover object-center" />
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black opacity-50" />
                        </div>
                        <div class="absolute inset-0 flex items-end rounded-lg p-6">
                            <div>
                                <p aria-hidden="true" class="text-sm text-white">Shop the collection</p>
                                <h3 class="mt-1 font-semibold text-white">
                                    <a :href="collection.href">
                                        <span class="absolute inset-0" />
                                        {{ collection.name }}
                                    </a>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
const collections = [
    {
        name: "Women's",
        href: '#',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/home-page-04-collection-01.jpg',
        imageAlt: 'Woman wearing an off-white cotton t-shirt.',
    },
    {
        name: "Men's",
        href: '#',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/home-page-04-collection-02.jpg',
        imageAlt: 'Man wearing a charcoal gray cotton t-shirt.',
    },
    {
        name: 'Desk Accessories',
        href: '#',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/home-page-04-collection-03.jpg',
        imageAlt: 'Person sitting at a wooden desk with paper note organizer, pencil and tablet.',
    },
]
</script>
