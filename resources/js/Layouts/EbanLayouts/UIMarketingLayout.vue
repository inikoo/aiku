<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Fri, 06 Jan 2023 14:42:07 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Inikoo Ltd
  -->
<script setup>
import {Popover, PopoverButton, PopoverPanel, PopoverGroup} from '@headlessui/vue';
import {ChevronDownIcon} from '@heroicons/vue/20/solid'
import LogoImage from "@/../../public/Images/AWAdvantageLogo.png";
import {Link} from "@inertiajs/vue3";


import {
    Bars3Icon,
    ChartBarIcon,
    CursorArrowRaysIcon,
    ShieldCheckIcon,
    Squares2X2Icon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import Footer from "@/Components/Footer.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const logIn = [
    {name: 'Products', route: 'ui-marketing.auth.login'},
    /*  {name: 'Features', route: 'ui-marketing.features'}, */
]

const signUp = [
    {name: 'Products', route: 'ui-marketing.auth.register'},
]


const navigation = [
    {name: 'Products', route: 'ui-marketing.home'},
    /*  {name: 'Features', route: 'ui-marketing.features'}, */

];

const aboutLink = [
    {name: 'Products', route: 'ui-marketing.about'},
    /*  {name: 'Features', route: 'ui-marketing.features'}, */

];

const contactLink = [
    {name: 'Products', route: 'ui-marketing.contact'},
    /*  {name: 'Features', route: 'ui-marketing.features'}, */

];

const navigationTwo = [
    {name: 'Company', route: 'ui-marketing.company'},

]


const solutions = [
    /*{
        name: 'Ads',
        description: 'Hello',
        route: 'ui-marketing.products.adwords',
        icon: ChartBarIcon,
    },
    {
        name: 'Seo',
        description: 'Hello',
        route: 'ui-marketing.products.seo',
        icon: CursorArrowRaysIcon,
    },
    {
        name: 'Email Marketing',
        description: 'Hello',
        route: 'ui-marketing.products.emailmarketing',
        icon: ShieldCheckIcon
    },
    {
        name: 'Bannersnack',
        description: 'Hello',
        route: 'ui-marketing.products.banners',
        icon: Squares2X2Icon,
    },*/
    {
        name: 'CategoryFilters',
        description: 'Hello',
        route: 'ui-marketing.categoryfilters',
        icon: ChartBarIcon,
    },
    {
        name: 'CategoryPreview',
        description: 'Hello',
        route: 'ui-marketing.categorypreviews',
        icon: CursorArrowRaysIcon,
    },
    {
        name: 'CheckoutForms',
        description: 'Hello',
        route: 'ui-marketing.checkoutforms',
        icon: ShieldCheckIcon
    },
    {
        name: 'Incentives',
        description: 'Hello',
        route: 'ui-marketing.incentives',
        icon: Squares2X2Icon,
    },
    {
        name: 'OrderHistory',
        description: 'Hello',
        route: 'ui-marketing.orderhistory',
        icon: ChartBarIcon,
    },
    {
        name: 'OrderSummaries',
        description: 'Hello',
        route: 'ui-marketing.ordersummary',
        icon: CursorArrowRaysIcon,
    },
    {
        name: 'ProductFeatures',
        description: 'Hello',
        route: 'ui-marketing.productfeatures',
        icon: ShieldCheckIcon
    },
    {
        name: 'ProductList',
        description: 'Hello',
        route: 'ui-marketing.productlist',
        icon: Squares2X2Icon,
    },
    {
        name: 'ProductOverviews',
        description: 'Hello',
        route: 'ui-marketing.productoverview',
        icon: ChartBarIcon,
    },
    {
        name: 'PromoSections',
        description: 'Hello',
        route: 'ui-marketing.promosections',
        icon: ShieldCheckIcon
    },
    {
        name: 'ShoppingCarts',
        description: 'Hello',
        route: 'ui-marketing.shoppingcarts',
        icon: Squares2X2Icon,
    }
]

</script>

<template>

    <div>
        <Popover class=" mx-auto  ">
            <div
                class=" flex max-w-8xl  mb-12  ml-5  items-center justify-between pt-1 md:justify-start md:space-x-10 lg:flex ">
                <PopoverGroup as="nav" class="hidden space-x-10 md:flex  ml-5">
                    <Link v-for="item in navigation" :key="item.name" :href="route(item.route)"
                          class="text-base font-medium text-gray-500 hover:text-gray-900 hover:underline focus:underline">
                        Home
                    </Link>
                    <Popover class="relative" v-slot="{ open }">
                        <PopoverButton class=""

                            :class="[open ? 'text-gray-900' : 'text-gray-500', 'group inline-flex items-center rounded-md text-base font-medium hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-black focus:ring-offset-2']">
                            <span>Ecommerce</span>
                            <ChevronDownIcon
                                :class="[open ? '-rotate-180' : 'rotate-0 ','transform','text-gray-400', 'ml-2 h-5 w-5 group-hover:text-gray-500']"
                                aria-hidden="true"/>
                        </PopoverButton>

                        <transition enter-active-class="transition ease-out duration-200"
                                    enter-from-class="opacity-0 translate-y-1"
                                    enter-to-class="opacity-100 translate-y-0"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="opacity-100 translate-y-0"
                                    leave-to-class="opacity-0 translate-y-1">
                            <PopoverPanel
                                class="absolute z-10 -ml-4 mt-3 w-screen max-w-md transform lg:left-1/2 lg:ml-44 lg:max-w-2xl lg:-translate-x-1/2">
                                <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                                    <div class="relative grid gap-6 bg-white px-5 py-6 sm:gap-8 sm:p-8 lg:grid-cols-2">
                                        <Link v-for="item in solutions" :key="item.name" :href="route(item.route)"
                                              class="-m-3 flex items-start rounded-lg p-3 hover:bg-gray-50">
                                            <div
                                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-md bg-gradient-to-r from-green-500 to-yellow-300 bg-origin-border text-black sm:h-12 sm:w-12 hover:from-green-600 hover:to-yellow-400">
                                                <component :is="item.icon" class="h-6 w-6" aria-hidden="true"/>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-base font-medium text-gray-900 ">{{ item.name }}</p>
                                                <p class="mt-1 text-sm text-gray-500">{{ item.description }}</p>
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </PopoverPanel>
                        </transition>
                    </Popover>
                    <Link v-for="item in aboutLink" :key="item.name" :href="route(item.route)"
                          class=" text-base font-medium text-gray-500 hover:text-gray-900 hover:underline focus:underline">
                        About
                    </Link>
                    <Link v-for="item in contactLink" :key="item.name" :href="route(item.route)"
                          class=" text-base font-medium text-gray-500 hover:text-gray-900 hover:underline focus:underline">
                        Contact
                    </Link>
                </PopoverGroup>
                <div class="flex  justify-end lg:w-0 lg:flex-1">
                    <Link :href="route('ui-marketing.home')">
                        <!-- <span class="sr-only">Your Company</span> -->
                        <img class="object-cover" width="350" height="350" :src='LogoImage' alt=""/>
                    </Link>
                </div>
                <div class="-my-2 mr-7 md:hidden">
                    <PopoverButton
                        class="inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <span class="sr-only">Open menu</span>
                        <Bars3Icon class="h-6 w-6" aria-hidden="true"/>
                    </PopoverButton>
                </div>
                <div class="hidden items-center justify-end md:flex md:flex-1 lg:w-0 pr-5">
                    <Link v-for="item in logIn" :key="item.name" :href="route(item.route)"
                          class="whitespace-nowrap  text-base font-medium text-gray-500 hover:text-gray-900"> Sign
                        in
                    </Link>
                    <Link v-for="item in signUp" :key="item.name" :href="route(item.route)">
                        <PrimaryButton>Sign Up</PrimaryButton>
                    </Link>
                </div>
            </div>
            <transition enter-active-class="duration-200 ease-out" enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100" leave-active-class="duration-100 ease-in"
                        leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
                <PopoverPanel focus
                              class="absolute inset-x-0 top-0 z-30 origin-top-right transform p-2 transition md:hidden">
                    <div
                        class="divide-y-2 divide-gray-50 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="px-5 pt-5 pb-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <img class="h-8 w-auto"
                                         src="https://tailwindui.com/img/logos/mark.svg?from-color=purple&from-shade=600&to-color=indigo&to-shade=600&toShade=600"
                                         alt="Your Company"/>
                                </div>
                                <div class="-mr-2">
                                    <PopoverButton
                                        class="inline-flex items-center justify-center rounded-md bg-white p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                                        <span class="sr-only">Close menu</span>
                                        <XMarkIcon class="h-6 w-6" aria-hidden="true"/>
                                    </PopoverButton>
                                </div>
                            </div>
                            <div class="mt-6">
                                <nav class="grid grid-cols-1 gap-7 ">
                                    <Link v-for="item in solutions" :key="item.name" :href="route(item.route)"
                                          class="-m-3 flex items-center rounded-lg p-3 hover:bg-gray-50">
                                        <div
                                            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-md   bg-gradient-to-r from-green-500 to-yellow-300 bg-origin-border text-black hover:from-green-600 hover:to-yellow-300">
                                            <component :is="item.icon" class="h-6 w-6" aria-hidden="true"/>
                                        </div>
                                        <div class="ml-4 text-base font-medium text-gray-900">{{ item.name }}</div>
                                    </Link>
                                </nav>
                            </div>
                        </div>
                    </div>
                </PopoverPanel>
            </transition>
        </Popover>
        <main class="mx-auto">
            <slot></slot>
        </main>
        <Footer></Footer>
    </div>

</template>

