<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Wed, 07 Jun 2023 02:45:27 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { faCube, faLink } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { ref } from "vue"
import Button from '@/Components/Elements/Buttons/Button.vue';

library.add(faCube, faLink)

const props = defineProps<{
    modelValue: any
}>()

const message = ref('Hello Vue!')

const dataProduct = ref({
    productsList: [
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-04-trending-product-01.jpg',
            ready: false,
            stock: 41,
            label: 'Coming soon'
        },
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-04-trending-product-02.jpg',
            ready: true,
            stock: 41,
            label: 'Coming soon'
        },
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-04-trending-product-03.jpg',
            ready: true,
            stock: 5,
            label: 'Coming soon'
        },
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-04-trending-product-04.jpg',
            ready: false,
            stock: 0,
            label: 'Out Of Stock',
            eta: '05.09.2024'
        },
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-04-trending-product-01.jpg',
            ready: false,
            stock: 41,
            label: 'Coming soon'
        },
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-04-trending-product-02.jpg',
            ready: true,
            stock: 41,
            label: 'Coming soon'
        },
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-04-trending-product-03.jpg',
            ready: true,
            stock: 5,
            label: 'Coming soon'
        },
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-04-trending-product-04.jpg',
            ready: false,
            stock: 0,
            label: 'Out Of Stock',
            eta: '05.09.2024'
        },
    ]
})

const dataProductlike = ref({
    productsList: [
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-02-product-01.jpg',
            ready: false,
            stock: 41,
            label: 'Coming soon'
        },
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-02-product-02.jpg',
            ready: true,
            stock: 41,
            label: 'Coming soon',
            promo: 'buy 2 get 1 free'
        },
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-02-product-03.jpg',
            ready: true,
            stock: 5,
            label: 'Coming soon',
            promo: 'buy 2 get 1 free'
        },
        {
            image: 'https://tailwindui.com/img/ecommerce-images/home-page-02-product-04.jpg',
            ready: false,
            stock: 0,
            label: 'Out Of Stock',
            promo: 'buy 2 get 1 free',
            eta: '05.09.2024'
        },
    ]
})

const selectedProduct = ref(0)

const decrease = () => {
    selectedProduct.value - 1
}


</script>

<template>
    <div class="w-full">
        <nav class="text-gray-500 mb-4">
            Beauty & Spa >> Bath Bombs
        </nav>

        <div class="aspect-[1902/683] mb-6 overflow-hidden flex items-center">
            <img src="https://tailwindui.com/img/ecommerce-images/home-page-03-category-02.jpg" class="w-full"
                alt="Product Image">
        </div>

        <div class="py-6 relative flex items-center justify-center">
            <div class="flex items-center justify-center space-x-8">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-leaf text-xl text-gray-600"></i>
                    <span class="text-sm text-gray-600">Vegan</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-hand-holding-heart text-xl text-gray-600"></i>
                    <span class="text-sm text-gray-600">Handmade</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-paw text-xl text-gray-600"></i>
                    <span class="text-sm text-gray-600">Cruelty Free</span>
                </div>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-recycle text-xl text-gray-600"></i>
                    <span class="text-sm text-gray-600">Plastic Free</span>
                </div>
            </div>

            <!-- Section  -->
            <div class="bg-white absolute -top-12 right-1 w-64 mr-1 p-4">
                <h3 class="text-sm font-bold mb-2">NOT A MEMBER?</h3>
                <p class="text-xs text-orange-600 mb-2">Order 4 or more outers from this product family to benefit from
                    lower price.</p>
                <a href="#" class="text-xs font-bold underline">Membership Informations</a>
            </div>
        </div>
    </div>

    <div class="w-full p-6">
        <h1 class="text-3xl font-bold mb-4">Shaped Bath Bombs For Kids</h1>
        <p class="text-lg text-gray-700 mb-4">Introducing our <strong>Wholesale Monkey Shaped Bath Bomb</strong>
            weighing 90g and infused with the delightful fusion of Guava & Strawberry. This bulk package is perfect for
            retailers, spas, or anyone looking to add a touch of fun and tropical fragrance to their bath and body
            products.</p>
        <p class="text-lg text-gray-700 mb-4">Each bath bomb is meticulously crafted into an adorable monkey shape,
            bringing a sense of whimsy and playfulness to any bathing routine. The attention to detail and vibrant
            colours make these bath bombs visually appealing and irresistible to customers of all ages.</p>
        <a href="#" class="text-lg font-bold underline mb-6 inline-block">Read More</a>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-x-8 gap-y-6">
            <div v-for="product in dataProduct.productsList" class="relative ">
                <i
                    class="absolute top-2 right-4 text-3xl far fa-heart text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                <img :src="product.image" alt="Product Image" class="w-full mb-2">
                <p class="text-sm text-gray-600 font-bold">8x Monkey Bath Bomb 90g - Guava & Strawberry</p>

                <div class="flex items-end justify-between">
                    <p class="leading-none text-sm text-gray-600">SKB-01</p>
                    <p class="leading-none text-xs text-gray-600">RRP: £3.95/Piece</p>
                </div>

                <div class="mb-1 flex items-center justify-between">
                    <div>
                        <i class="fas fa-circle cursor-pointer"
                            :class="product.stock ? product.stock > 5 ? 'text-green-600' : 'text-orange-500' : 'text-red-600'"></i>
                        <span class="text-xs text-gray-400"> ({{ product.stock }})</span>
                    </div>
                    <p class="justify-self-end text-sm text-gray-600">★★★★★ <span class="text-xs text-gray-400">({{
                            product.stock }})</span>
                    </p>
                </div>

                <div class="mb-2 space-y-2">
                    <div class="text-sm font-bold leading-none whitespace-nowrap">
                        £9.60 (1.20/Piece)
                    </div>
                    <div class="text-sm text-orange-500 font-bold leading-none whitespace-nowrap">
                        £8.00 (1.00/Piece)
                    </div>
                </div>

                <div class="mb-4 flex items-center justify-between">
                    <div class="bg-orange-500 px-2 py-0.5 rounded w-fit text-xs text-white">
                        Member Price
                    </div>

                </div>

                <div class="w-fit flex gap-x-4 mb-4">
                    <div class="flex items-center gap-x-2">
                        <i class="fas fa-minus cursor-pointer"></i>
                        <div
                            class="h-8 aspect-square border border-gray-400 flex items-center justify-center tabular-nums text-xl font-bold">
                            1</div>
                        <i class="fas fa-plus cursor-pointer"></i>
                    </div>

                    <div class="flex flex-col items-end">
                        <div v-if="!product.ready" class="-mt-6">
                            <i class="far fa-envelope cursor-pointer"></i>
                            Notify Me
                        </div>

                        <div class="text-white px-6 py-1 h-fit w-fit"
                            :class="[product.ready ? 'bg-gray-600' : 'bg-gray-300']">
                            {{ product.ready ? 'Order Now' : product.label }}
                        </div>

                        <div v-if="product.eta" class="absolute -bottom-1 text-gray-400 italic text-sm">
                            ETA: {{ product.eta }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="col-span-3 h-full relative overflow-hidden">
                <img src="https://tailwindui.com/img/ecommerce-images/home-page-01-hero-full-width.jpg" alt="Product Showcase Image" class="absolute w-full ">
            </div> -->
        </div>
    </div>

    <!-- Section: You might also like -->
    <div class="w-full pt-12 pb-16">
        <h2 class="text-2xl font-bold mb-6 text-center">You might also like:</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-x-8 gap-y-6">
            <div v-for="product in dataProductlike.productsList" class="relative flex flex-col justify-between ">
                <div>
                    <i
                        class="absolute top-2 right-4 text-3xl far fa-heart text-gray-400 hover:text-gray-600 cursor-pointer"></i>
                    <img :src="product.image" alt="Product Image" class="w-full mb-2">
                    <p class="text-sm text-gray-600 font-bold">8x Monkey Bath Bomb 90g - Guava & Strawberry</p>
                    <div class="flex items-end justify-between">
                        <p class="leading-none text-sm text-gray-600">SKB-01</p>
                        <p class="leading-none text-xs text-gray-600">RRP: £3.95/Piece</p>
                    </div>
                    <!-- Stock and Stars -->
                    <div class="mb-2 flex items-center justify-between">
                        <div>
                            <i class="fas fa-circle cursor-pointer"
                                :class="product.stock ? product.stock > 5 ? 'text-green-600' : 'text-orange-500' : 'text-red-600'"></i>
                            <span class="text-xs text-gray-400"> ({{ product.stock }})</span>
                        </div>
                        <p class="justify-self-end text-sm text-gray-600">★★★★★ <span class="text-xs text-gray-400">({{
                                product.stock }})</span>
                        </p>
                    </div>
                    <div v-if="product.promo"
                        class="uppercase mb-2.5 bg-green-600 rounded-sm text-white px-2 w-fit font-bold">
                        {{ product.promo }}
                    </div>
                </div>

                <div>
                    <div class="mb-2 space-y-2">
                        <div class="text-sm font-bold leading-none whitespace-nowrap">
                            £9.60 (1.20/Piece)
                        </div>
                        <div class="text-sm text-orange-500 font-bold leading-none whitespace-nowrap">
                            £8.00 (1.00/Piece)
                        </div>
                    </div>
                    <div class="mb-4 flex items-center justify-between">
                        <div class="bg-orange-500 px-2 py-0.5 rounded w-fit text-xs text-white">
                            Member Price
                        </div>
                    </div>
                    <div class="w-fit flex gap-x-4 mb-4">
                        <div class="flex items-center gap-x-2">
                            <i class="fas fa-minus cursor-pointer"></i>
                            <div
                                class="h-8 aspect-square border border-gray-400 flex items-center justify-center tabular-nums text-xl font-bold">
                                1</div>
                            <i class="fas fa-plus cursor-pointer"></i>
                        </div>
                        <div class="flex flex-col items-end">
                            <div v-if="!product.ready" class="-mt-6">
                                <i class="far fa-envelope cursor-pointer"></i>
                                Notify Me
                            </div>
                            <div class="text-white px-6 py-1 h-fit w-fit"
                                :class="[product.ready ? 'bg-gray-600' : 'bg-gray-300']">
                                {{ product.ready ? 'Order Now' : product.label }}
                            </div>
                            <div v-if="product.eta" class="absolute -bottom-1 text-gray-400 italic text-sm">
                                ETA: {{ product.eta }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="w-full bg-gray-600 py-4 px-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="col-span-2 relative">
                <img src="https://tailwindui.com/img/ecommerce-images/home-page-01-hero-full-width.jpg"
                    alt="Informative Image" class="w-full h-full object-cover rounded-md">
                <!-- <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                    <i class="fas fa-play text-white/90 text-6xl"></i>
                </div> -->
            </div>

            <div class="flex flex-col px-14 pt-14">
                <h2 class="text-lg font-bold text-white mb-10">What ingredients should be considered when selecting body
                    and hand soaps?</h2>
                <p class="text-[9px] text-white mb-14">Provide your customers with informed advice.</p>
                <button class="place-self-center bg-white text-gray-800 py-2 px-8 w-max">Read The Blog</button>
            </div>
        </div>
    </div>

    <div class="w-full py-12 flex gap-x-10">
        <h2 class="text-2xl font-bold">Reviews:</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-10">
            <div v-for="index in 3" class="">
                <h3 class="font-bold text-lg">Johnn Smith <span class="ml-6 font-normal"><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i> 41</span></h3>
                <p class="mt-2 text-sm text-justify">Thank you so much for taking the time to give Ancient Wisdom
                    Wholesale a fantastic 5-star rating! We truly appreciate your support and are thrilled to hear that
                    you love our products.</p>
                <p class="mt-2 text-sm">From the moment you place your order to the prompt</p>
            </div>
        </div>
    </div>
</template>