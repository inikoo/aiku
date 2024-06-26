<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 22 Aug 2023 19:44:06 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { library } from "@fortawesome/fontawesome-svg-core"
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from "@headlessui/vue"
import { faHandPointer, faHandRock, faPlus } from "@fas"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import PureMultiselect from "@/Components/Pure/PureMultiselect.vue"
import { ref, watch } from "vue"

library.add(faHandPointer, faHandRock, faPlus)

const props = defineProps<{
    toolsBluprint: Array
    modelValue: Object
}>()
const emits = defineEmits()

const message = ref('Hello Vue!')
const dataProduct = ref()
const navigationsList = [
    {
        label: 'New In & Trending'
    },
    {
        label: 'Offers'
    },
    {
        label: 'Aromatherapy'
    },
    {
        label: 'Beauty & Spa',
        subnavs: [
            {
                title: "Beauty Products",
                links: [
                    { label: "Aromatherapy Hand & Body Lotion", url: '#' },
                    { label: "Fragrance Hand & Body Lotions", url: '#' },
                    { label: "Organic Body Oil", url: '#' },
                    { label: "Aromatherapy Shea Body Butters", url: '#' },
                    { label: "Pure Body Butters", url: '#' },
                    { label: "Scented Butters", url: '#' },
                    { label: "Fragranced Sugar Body Scrubs", url: '#' },
                    { label: "Organic Hair Serum", url: '#' },
                ]
            },
            {
                title: "Bath Bombs",
                links: [
                    { label: "Crystal Jewellery Bath Bomb", url: '#' },
                    { label: "Chakra Bath Fizzers Sets", url: '#' },
                    { label: "Valentines Bath Bombs", url: '#' },
                    { label: "Christmas Bath Bomb Gift Pack", url: '#' },
                    { label: "Bath Dust", url: '#' },
                    { label: "Cocktail Scented Bath Bombs", url: '#' },
                    { label: "Chill Pills Gift Packs", url: '#' },
                    { label: "Bath Bomb Kit", url: '#' },
                ]
            },
            {
                title: "Soaps",
                links: [
                    { label: "Artisan Olive Oil Soap", url: '#' },
                    { label: "Wild & Natural Soap Loaf", url: '#' },
                    { label: "Sliced Wild & Natural Soap Loaf", url: '#' },
                    { label: "Cool Waves Soap Loaves", url: '#' },
                    { label: "Essential Oil Soap Loaf", url: '#' },
                    { label: "Designer Soap Loaf", url: '#' },
                    { label: "Loofah - Star Soap Loaf", url: '#' },
                    { label: "Loofah - Round Soap Loaf", url: '#' },
                ]
            },
            {
                title: "Shop By Products",
                links: [
                    { label: "Gift Sets / Hampers", url: '#' },
                    { label: "Bath Bombs", url: '#' },
                    { label: "Soaps", url: '#' },
                    { label: "Soap Flowers", url: '#' },
                    { label: "Products with Gemstones", url: '#' },
                    { label: "Shampoos", url: '#' },
                    { label: "Body Lotions & Butters", url: '#' },
                ]
            },
            {
                title: "Beauty Accessories",
                links: [
                    { label: "Gemstone Face Rollers", url: '#' },
                    { label: "Teardrop Konjac Sponge", url: '#' },
                    { label: "Natural Japan Style Konjac Sponge", url: '#' },
                    { label: "Egyptian Luxury Loofah", url: '#' },
                    { label: "Glove, Mitts & Wraps", url: '#' },
                    { label: "Fun Fruit Shape Sponges", url: '#' },
                    { label: "Natural Soap Bags and Scrunches", url: '#' },
                ]
            },
            {
                title: "Bath Salts & Florals",
                links: [
                    { label: "Bath Salts in Vials", url: '#' },
                    { label: "Wild Hare Salt & Flowers Sets", url: '#' },
                    { label: "Himalayan Bath Salt Blends", url: '#' },
                    { label: "Aromatherapy Bath Potion in Kraft Bags", url: '#' },
                    { label: "Floral Bath Soak & Facial Steam Blend", url: '#' },
                    { label: "Aromatherapy Bath Potion - 7 kg", url: '#' },
                    { label: "Himalayan Bath Salt - 25 kg", url: '#' },
                ]
            },
            {
                title: "Soap Flowers",
                links: [
                    { label: "Soap Flowers Gift Boxes", url: '#' },
                    { label: "Gift Soap Flower Bouquet", url: '#' },
                    { label: "Ready to Retail Soap Flowers", url: '#' },
                    { label: "Petite Soap Flower Bouquets", url: '#' },
                    { label: "Luxury Soap Flowers", url: '#' },
                    { label: "Soap Flower Bouquets", url: '#' },
                    { label: "Craft Soap Flowers", url: '#' },
                ]
            },
            {
                title: "Fragrance",
                links: [
                    { label: "Fine Fragrance Perfume Oils", url: '#' }
                ]
            }
        ]
    },
    {
        label: 'Accessories'
    },
    {
        label: 'Artisan Tea'
    },
    {
        label: 'Home Fragrance'
    },
    {
        label: 'Home & Garden'
    },
    {
        label: 'Gemstones & Esoteric Gifts'
    },
    {
        label: 'Incense'
    },
    {
        label: 'Displays & Packaging'
    },
]

const toggleChildClass = (element) => {
    element.currentTarget.querySelector('ul').classList.toggle('hidden')
}

</script>

<template>
    <!-- Top Bar -->
    <div class="bg-gray-800 grid grid-cols-3 text-white flex justify-between items-center p-2 text-xs">
        <div></div>
        <div class="font-bold text-center">FAIRLY TRADING WHOLESALE GIFTS SINCE 1995</div>

        <!-- Section: Logout, Cart, profile -->
        <div class="place-self-end flex items-center space-x-4 mr-4">
            <a href="#" class="flex items-center">
                <i class="fas fa-sign-out-alt mr-1"></i> Log Out
            </a>
            <a href="#">
                <i class="far fa-heart"></i>
            </a>
            <a href="#" class="flex items-center gap-x-1">
                <i class="fas fa-shopping-cart relative mr-1">
                    <div
                        class="absolute -top-1 -right-1 bg-white border border-gray-800 h-2.5 aspect-square rounded-full text-gray-600 text-[6px] flex items-center justify-center">
                        5
                    </div>
                </i> £568.20
            </a>
            <a href="#" class="flex items-center"><i class="fas fa-user-circle mr-1"></i> Hello Sandra</a>
        </div>
    </div>


    <!-- Main Nav -->
    <div class="bg-white shadow-md border-b-2 border-gray-700">
        <div class="container mx-auto flex flex-col justify-between items-center">
            <div class="w-full grid grid-cols-3 items-center justify-between space-x-4">
                <img src="https://d19ayerf5ehaab.cloudfront.net/assets/store-18687/18687-logo-1642004490.png"
                    alt="Ancient Wisdom Logo" class="h-24">
                <div class="relative w-fit justify-self-center">
                    <input type="text" placeholder="Search Products"
                        class="border border-gray-400 py-1 px-4 text-sm w-80">
                    <i class="fas fa-search absolute top-1/2 -translate-y-1/2 right-4 text-gray-400"></i>
                </div>
                <button
                    class="justify-self-end w-fit bg-stone-500 hover:bg-stone-600 text-white text-sm py-1 px-4 rounded-md">Become
                    a Gold Reward Member <i class="ml-1 fas fa-chevron-right text-xs"></i> </button>
            </div>

            <!-- Section: Navigation list horizontal -->
            <nav class="relative flex text-sm text-gray-600">
                <div v-for="(navigation, idxNavigation) in navigationsList" href="#" class="group w-full ">
                    <div
                        class="px-5 hover:bg-gray-200 hover:text-orange-500 flex items-center justify-center gap-x-1 h-full cursor-pointer">
                        <div class="w-fit text-center">{{ navigation.label }}</div>
                        <i class="fas fa-chevron-down text-[11px]"></i>
                    </div>

                    <!-- Section: Subnav hover -->
                    <div v-if="navigation.subnavs"
                        class="hidden group-hover:grid inline absolute left-0 top-full border border-gray-300 w-full grid-cols-4 gap-x-5 gap-y-8 px-6 pt-6 pb-14">
                        <div v-for="subnav in navigation.subnavs" class="space-y-2">
                            <div class="font-semibold">{{ subnav.title }}</div>

                            <!-- Subnav links -->
                            <div class="flex flex-col gap-y-2">
                                <div v-for="link in subnav.links" class="flex items-center gap-x-2">
                                    <i class="fas fa-chevron-right text-[10px] text-gray-400"></i>
                                    <a :href="link.url"
                                        class=" text-gray-500 hover:text-gray-600 hover:underline cursor-pointer">
                                        {{ link.label }}
                                    </a>
                                </div>

                                <div v-if="subnav.title != 'Fragrance'"
                                    class="font-semibold underline text-xs cursor-pointer">
                                    See all
                                </div>
                                <div v-else class="mt-6">
                                    <div class="font-bold underline">
                                        Starters
                                    </div>
                                    <div class="mt-2">
                                        Shop Beauty & Spa Starters
                                    </div>
                                    <div class="mt-6 underline font-semibold">
                                        BLOG - AW Product Guide
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</template>
