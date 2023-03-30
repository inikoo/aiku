<template>
    <div class="bg-white">
        <div class="mx-auto max-w-xl py-16 px-4 sm:py-24 sm:px-6 lg:max-w-7xl lg:px-8">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Shop by Collection</h2>
            <p class="mt-4 text-base text-gray-500">Each season, we collaborate with world-class designers to create a collection inspired by the natural world.</p>

            <div class="mt-10 space-y-12 lg:grid lg:grid-cols-3 lg:gap-x-8 lg:space-y-0">
                <a v-for="category in categories" :key="category.name" :href="category.href" class="group block">
                    <div aria-hidden="true" class="aspect-w-3 aspect-h-2 overflow-hidden rounded-lg group-hover:opacity-75 lg:aspect-w-5 lg:aspect-h-6">
                        <img :src="category.imageSrc" :alt="category.imageAlt" class="h-full w-full object-cover object-center" />
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">{{ category.name }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ category.description }}</p>
                </a>
            </div>
        </div>
    </div>
</template>

<script setup>
const categories = [
    {
        name: 'Handcrafted Collection',
        href: '#',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/home-page-01-collection-01.jpg',
        imageAlt: 'Brown leather key ring with brass metal loops and rivets on wood table.',
        description: 'Keep your phone, keys, and wallet together, so you can lose everything at once.',
    },
    {
        name: 'Organized Desk Collection',
        href: '#',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/home-page-01-collection-02.jpg',
        imageAlt: 'Natural leather mouse pad on white desk next to porcelain mug and keyboard.',
        description: 'The rest of the house will still be a mess, but your desk will look great.',
    },
    {
        name: 'Focus Collection',
        href: '#',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/home-page-01-collection-03.jpg',
        imageAlt: 'Person placing task list card into walnut card holder next to felt carrying case on leather desk pad.',
        description: 'Be more productive than enterprise project managers with a single piece of paper.',
    },
]
</script>
