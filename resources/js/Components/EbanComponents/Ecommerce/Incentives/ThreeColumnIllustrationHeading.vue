<template>
    <div class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-gray-50 px-6 py-16 sm:p-16">
                <div class="mx-auto max-w-xl lg:max-w-none">
                    <div class="text-center">
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900">We built our business on customer service</h2>
                    </div>
                    <div class="mx-auto mt-12 grid max-w-sm grid-cols-1 gap-y-10 gap-x-8 sm:max-w-none lg:grid-cols-3">
                        <div v-for="incentive in incentives" :key="incentive.name" class="text-center sm:flex sm:text-left lg:block lg:text-center">
                            <div class="sm:flex-shrink-0">
                                <div class="flow-root">
                                    <img class="mx-auto h-16 w-16" :src="incentive.imageSrc" alt="" />
                                </div>
                            </div>
                            <div class="mt-3 sm:mt-0 sm:ml-6 lg:mt-6 lg:ml-0">
                                <h3 class="text-sm font-medium text-gray-900">{{ incentive.name }}</h3>
                                <p class="mt-2 text-sm text-gray-500">{{ incentive.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const incentives = [
    {
        name: 'Free shipping',
        imageSrc: 'https://tailwindui.com/img/ecommerce/icons/icon-shipping-simple.svg',
        description: "It's not actually free we just price it into the products. Someone's paying for it, and it's not us.",
    },
    {
        name: '10-year warranty',
        imageSrc: 'https://tailwindui.com/img/ecommerce/icons/icon-warranty-simple.svg',
        description: "If it breaks in the first 10 years we'll replace it. After that you're on your own though.",
    },
    {
        name: 'Exchanges',
        imageSrc: 'https://tailwindui.com/img/ecommerce/icons/icon-exchange-simple.svg',
        description:
            "If you don't like it, trade it to one of your friends for something of theirs. Don't send it here though.",
    },
]
</script>
