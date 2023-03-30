<template>
    <div class="bg-white">
        <div class="mx-auto max-w-2xl py-24 px-4 sm:py-32 sm:px-6 lg:max-w-7xl lg:px-8">
            <div class="max-w-3xl">
                <h2 id="features-heading" class="font-medium text-gray-500">Focus</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Simple productivity</p>
                <p class="mt-4 text-gray-500">Focus allows you to plan 10 daily tasks, while also thinking ahead about what's next. Forget distracting digital apps and embrace these small, sturdy pieces of paper.</p>
            </div>

            <div class="mt-11 grid grid-cols-1 items-start gap-y-16 gap-x-6 sm:mt-16 sm:grid-cols-2 lg:grid-cols-4 lg:gap-x-8">
                <div v-for="feature in features" :key="feature.name" class="flex flex-col-reverse">
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-gray-900">{{ feature.name }}</h3>
                        <p class="mt-2 text-sm text-gray-500">{{ feature.description }}</p>
                    </div>
                    <div class="aspect-w-1 aspect-h-1 overflow-hidden rounded-lg bg-gray-100">
                        <img :src="feature.imageSrc" :alt="feature.imageAlt" class="object-cover object-center" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const features = [
    {
        name: 'Three card types',
        description: 'Today, Next, and Someday cards allow you to defer your dreams into the future.',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/product-feature-08-detail-01.jpg',
        imageAlt: 'Green cardstock box containing white, beige, and brown cards.',
    },
    {
        name: 'The perfect mix',
        description: 'Each refill pack contains plenty of cards to last you a month of procrastination.',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/product-feature-08-detail-02.jpg',
        imageAlt: 'Green cardstock box open with 50 cards inside.',
    },
    {
        name: 'Dot grid backs',
        description: 'Flip a card over to doodle during meetings when you should be listening.',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/product-feature-08-detail-03.jpg',
        imageAlt: 'Detail of white today card, beige next card, and brown someday card with dot grid.',
    },
    {
        name: 'Refill packs',
        description: 'Subscribe and save on routine refill packs to keep you productive all year long.',
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/product-feature-08-detail-04.jpg',
        imageAlt: 'Stack of three green cardstock boxes with 3 hole cutouts showing cards inside.',
    },
]
</script>
