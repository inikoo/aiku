<script setup lang='ts'>
import Button from '@/Components/Elements/Buttons/Button.vue'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faLongArrowAltDown } from '@fal'
import { faStar, faCircle, faHandPointer } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import PureInputNumber from '@/Components/Pure/PureInputNumber.vue'
library.add(faLongArrowAltDown, faStar, faCircle, faHandPointer)

const descriptionProduct = `
    <div id="webpage_content_header_text" class="text-gray-600" style="">
        <h1 class="text-xl font-bold">Wholesale Nag Champa Incense</h1>
        <div class="description mt-4"><p style="text-align: justify;">Nag Champa is probably the best selling incense on the planet! Everyone should stock it as an essential item. &nbsp;<a class="_gre_ _str_" href="/tips-how-to-sell-more-nag-champa-incense" rel="noopener noreferrer" target="_blank"></a>
            <a class="_gre_" href="https://www.ancientwisdom.biz/how-to-sell-nag-champa-incense"><strong>Check out our Sales Tips Page</strong>.</a> The original Blue pack made famous by the patronage of Satya Sai Baba is now only one of a whole range of incense and other scented products made with this distinctive fragrance.</p>
            <p style="text-align: justify;">Sticks: Each pack contains 15 Incense Sticks. &nbsp;</p>
            <p class="mb-4">Approx. burn time: 30 minutes. Cones: Each pack contains 12 Incense Cones. Approx. cone burn time: 20 minutes. Use with an incense holder for best results.&nbsp;</p>
            <p class="text-lg"><strong>Made in Bangalore</strong>
            </p>
            <p style="text-align: justify;"><strong><span style="color: rgb(209, 72, 65);">Make sure you order plenty and make a big stack</span></strong></p>
        </div>
    </div>`


const productsList = [
    {
        title: "12x Nag Champa 15g",
        image: "https://www.ancientwisdom.biz/rwi/320x280_1842044.jpeg",
        code: "EID-04",
        rrp: "RRP : £1.35/pack",
        original_price: {
            price: "£4.68",
            rrp: "£0.39/pack"
        },
        discount: {
            percentage: 5,
            price: "£4.45",
            rrp: "£0.37/pack"
        }
    },
    {
        title: "600x Nag Champa 15g (Full Carton - 50 boxes of 12)",
        image: "https://www.ancientwisdom.biz/rwi/320x280_1842042.jpeg",
        code: "EID-04C",
        rrp: "RRP : £1.35/pack",
        original_price: {
            price: "£4.68",
            rrp: "£0.39/pack"
        },
        discount: {
            percentage: 5,
            price: "£4.45",
            rrp: "£0.37/pack"
        }
    },
    {
        title: "4x Satya Nagchampa Incense 250 Gms",
        image: "https://www.ancientwisdom.biz/rwi/320x280_1842018.jpeg",
        code: "EID-51",
        rrp: "RRP : £24.00/Pack",
        original_price: {
            price: "£44.94",
            rrp: "£11.24/Pack"
        },
        discount: {
            percentage: 5,
            price: "£42.69",
            rrp: "£10.67/Pack"
        }
    },
    {
        title: "42x Satya Assorted Incense 15 gms - Display Pack",
        image: "https://www.ancientwisdom.biz/rwi/320x280_1851953.jpeg",
        code: "EID-65",
        rrp: "RRP : £2.65/Pack",
        original_price: {
            price: "£32.14",
            rrp: "£0.77/Pack"
        },
        discount: {
            percentage: 5,
            price: "£30.53",
            rrp: "£0.73/Pack"
        }
    },
]
</script>

<template>
    <div class="bg-white text-gray-700 mx-auto max-w-7xl w-full py-16 sm:py-24 space-y-16">
        <div class="px-4 sm:px-6">
            <div class="relative overflow-hidden rounded-lg lg:h-96">
                <div class="absolute inset-0">
                    <img src="https://www.ancientwisdom.biz/wi.php?id=1842040&s=690x690" alt="" class="h-full w-full object-cover object-center" />
                </div>

                <div aria-hidden="true" class="relative h-96 w-full lg:hidden" />
                <div aria-hidden="true" class="relative h-32 w-full lg:hidden" />

                <div class="absolute inset-x-0 bottom-0 rounded-bl-lg rounded-br-lg bg-white bg-opacity-75 p-6 backdrop-blur backdrop-filter sm:flex sm:items-center sm:justify-between lg:inset-x-auto lg:inset-y-0 lg:w-96 lg:flex-col lg:items-start lg:rounded-br-none lg:rounded-tl-lg">
                    <div v-html="descriptionProduct" class="text-gray-600 pr-3 overflow-y-auto mb-4">
                    </div>
                        <!-- <h2 class="text-xl font-bold text-white">Workspace Collection</h2>
                        <p class="mt-1 text-sm text-gray-300">Upgrade your desk with objects that keep you organized and clear-minded.</p> -->
                    <!-- <a href="#" class="mt-6 flex flex-shrink-0 items-center justify-center rounded-md border border-white border-opacity-25 bg-white bg-opacity-0 px-4 py-3 text-base font-medium text-white hover:bg-opacity-10 sm:ml-8 sm:mt-0 lg:ml-0 lg:w-full">View the collection</a> -->
                    <Button type="indigo" :key="1" label="View the collection" size="xl" full />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-x-2 gap-y-4 px-6">
            <div v-for="product in productsList" class="grid relative border border-gray-300 rounded-md pb-4 ">
                <FontAwesomeIcon icon='fas fa-circle' class='absolute top-2 right-2 text-green-500 animate-pulse' v-tooltip="'Ready stock'" fixed-width aria-hidden='true' />
                <div>
                    <img :src="product.image" alt="" class="h-auto w-full rounded-t-full">
                    <div class="place-self-start mx-4 mb-2 xl:mb-4">
                        <div class="min-h-12 mb-1 border border-gray-300 rounded text-gray-600 font-semibold py-2 px-2 text-center flex items-center justify-center">
                            {{ product.title }}
                        </div>
                        <div class="flex justify-between text-xxs text-gray-500 mb-1">
                            <div>{{ product.code }}</div>
                            <div>{{ product.rrp}}</div>
                        </div>
                        <div class="grid grid-cols-2 xl:grid-cols-3 gap-x-2 mb-2 xl:mb-0">
                            <div class="text-gray-600 text-sm">Price</div>
                            <div class="col-span-2 text-xs flex items-center justify-between text-gray-400">
                                <div class="line-through">{{ product.original_price.price }}</div>
                                <div class="line-through text-right">({{ product.original_price.rrp}})</div>
                            </div>
                        </div>
                    
                        <div class="grid grid-cols-2 xl:grid-cols-3 gap-x-2">
                            <div class="">
                                <div class="border border-green-500 bg-green-50 text-green-500 rounded flex items-center text-sm w-fit px-1">
                                    <FontAwesomeIcon icon='fas fa-star' class='' fixed-width aria-hidden='true' />
                                    <div class="flex items-center">
                                        <FontAwesomeIcon icon='fal fa-long-arrow-alt-down' size="sm" class='' fixed-width aria-hidden='true' />
                                        <span class="">{{ product.discount.percentage }}%</span>
                                    </div>
                    
                                </div>
                            </div>
                            <div class="col-span-2 text-sm flex items-center justify-between text-gray-500">
                                <div class="text-orange-500">{{ product.discount.price }}</div>
                                <div class="text-orange-500 text-right">({{ product.discount.rrp }})</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mx-4 flex flex-col xl:flex-row gap-y-2 gap-x-1.5 self-end">
                    <div class="w-full xl:w-20">
                        <PureInputNumber class="py-0" />
                    </div>
                    <Button label="Order now" icon="fas fa-hand-pointer" type="black" full />
                </div>

            </div>
        
        </div>
    </div>
</template>