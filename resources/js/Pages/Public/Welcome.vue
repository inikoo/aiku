<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Tue, 20 Sept 2022 15:08:44 Malaysia Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import PureInput from '@/Components/Pure/PureInput.vue'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faSearch, faHome, faInfoCircle, faPhone, faFile, faChevronDown } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import Button from '@/Components/Elements/Buttons/Button.vue'
import { faPhone as fasPhone, faBuilding, faEnvelope, faBalanceScale, faIndustry, faUserPlus, faSignIn } from '@fas'
import { ref } from 'vue'
library.add(faSearch, faHome, faInfoCircle, faPhone, faFile, faChevronDown, fasPhone, faBuilding, faEnvelope, faBalanceScale, faIndustry, faUserPlus, faSignIn)

const navigations = {
    home: {
        name: 'home',
        label: 'Home',
        href: '#',
        icon: 'fal fa-home',
        subNav: {
            homepage: {
                name: 'homepage',
                label: 'Homepage',
                href: '#',
                icon: 'fal fa-home',
            },
            about: {
                name: 'about',
                label: 'About',
                href: '#',
                icon: 'fal fa-info-circle',
            },
            contact: {
                name: 'contact',
                label: 'Contact',
                href: '#',
                icon: 'fal fa-phone',
            },
            tnc: {
                name: 'tnc',
                label: 'Terms & Conditions',
                href: '#',
                icon: 'fal fa-file',
            },
        }
    },
    fulfilment: {
        name: 'fulfilment',
        label: 'Fulfilment',
        href: '#',
        icon: 'fal fa-box',
        subNav: {
            storage: {
                name: 'storage',
                label: 'Pallet storage & distribution',
                href: '#',
                // icon: 'fal fa-box',
            },
            pickPack: {
                name: 'pickPack',
                label: 'Pick, Pack & dispatch',
                href: '#',
                // icon: 'fal fa-box',
            },
            repacking: {
                name: 'repacking',
                label: 'Repacking, reworking, etc',
                href: '#',
                // icon: 'fal fa-box',
            },
        },
    },
    pricing: {
        name: 'pricing',
        label: 'Pricing',
        href: '#',
        icon: 'fal fa-dollar-sign',
        subNav: {
            pricing: {
                name: 'pricing',
                label: 'Pricing Example',
                href: '#',
                // icon: 'fal fa-dollar-sign',
            },
        }
    },
}

const contentImages = [
    {
        label: 'South Yorkshire - Heart of the UK',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1830486',
        description: `Centrally located close to the M1 and with daily collections by all the UK's main courier companies Affinity Park is a perfect location for efficient UK wide distribution.`
    },
    {
        label: '100.000 Square Foot',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1830487',
        description: `Affinity Park is the main UK distribution hub for Ancient Wisdom (Europe's favourite giftware wholesaler for 20 years). With 10 container docking bays and over 6000 pallet locations at our fulfilment centre uk we can handle any project big or small. `
    },
    {
        label: 'Pallet & Parcel Delivery across UK',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1830488',
        description: `Use your own couriers or piggy back on our existing highly competitive contracts. Pallets and parcel deliveries plugged directly into the best UK carriers. Plus you can utilise the AW Dropshipping system,  with API's available for Shopify etc. Explore our order fulfilment services.`
    },
    {
        label: 'Storage Solutions',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1849917',
        description: `Store pallets & parcels hassle-free. Boost efficiency, reduce costs. Maximize space. We understand that every business is unique, and that's why we offer flexible storage options tailored to your specific needs.`
    },
    {
        label: 'Pick & Pack',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1849918',
        description: `Our pick and pack service delivers speed and precision, ensuring flawless order fulfillment uk. From warehouse to your customer's doorstep without errors and delays.`
    },
    {
        label: 'Rework & Repack',
        src: 'https://www.aw-fulfilment.co.uk/wi.php?id=1849919',
        description: `Your stock is mislabelled, packed incorrectly or missing barcodes? Elevate your brand with carefully curated products ensuring your peace of mind.`
    },
]

const contactAddress = {
    company: {
        label: "Ancient Wisdom Marketing Ltd. Affinity Park, Europa Drive   Sheffield, s9 1xt",
        icon: "fas fa-building"
    },
    email: {
        label: "info@aw-fulfilment.co.uk",
        icon: "fas fa-envelope"
    },
    phone: {
        label: "+44 (0)1142 729 165",
        icon: "fas fa-phone"
    },
    vat: {
        label: "Vat No:GB764298589",
        icon: "fas fa-balance-scale"
    },
    reg: {
        label: "Reg. No: 04108870",
        icon: "fas fa-industry"
    }
}

const families = [
    {
        label: "Ancient Wisdom",
        href: "https://www.ancientwisdom.biz",
    },
    {
        label: "AW Aromatics - UK",
        href: "https://www.aw-aromatics.com/",
    },
    {
        label: "AW Artisan Spain",
        href: "https://www.awartisan.eu/",
    },
    {
        label: "AW Gifts Europe",
        href: "https://www.awgifts.eu/",
    },
    {
        label: "AW Dropshipping - UK",
        href: "https://www.aw-dropship.com/",
    },
    {
        label: "AW Dropshipping - EU",
        href: "https://www.aw-dropship.eu/",
    },
    {
        label: "AW Fulfilment Europe",
        href: "https://www.aw-fulfilment.eu/",
    }
]

const footerAbout = [
    {
        label: "Contact Us",
        href: "https://www.aw-fulfilment.co.uk/contact.sys",
    },
    {
        label: "About Us",
        href: "https://www.aw-fulfilment.co.uk/about.sys",
    },
    {
        label: "Delivery",
        href: "https://www.aw-fulfilment.co.uk/shipping.sys",
    },
    {
        label: "Terms & Conditions",
        href: "https://www.aw-fulfilment.co.uk/tac.sys",
    }
]

const inputSearch = ref()
const inputValue = ref('')

</script>

<template>
    <Head :title="$t('Welcome to aiku')" />
    <div class="bg-white shadow-lg mx-auto max-w-7xl w-full">
        <!-- Section: Top header -->
        <div class="grid grid-cols-3 h-24 items-center">
            <div class="justify-self-start self-center"><img src="https://www.aw-fulfilment.co.uk/wi.php?id=1837721" class="h-20"></div>
            <div class="text-center place-self-center">
                <h3 class="text-amber-700 text-xl font-bold">Your UK's Best Fulfilment Warehouse</h3>
                <span class="text-amber-700">Storage - Pick & Pack - Distribution</span>
            </div>
            <div class="w-3/4 justify-self-end text-sm pr-4">
                <p class="text-gray-500 text-xs">We Can Fulfil Your Orders</p>
                <div class="flex gap-x-2 items-center relative">
                    <PureInput v-model="inputValue" placeholder="Search your needs" ref="inputSearch" />
                    <FontAwesomeIcon icon='fal fa-search' @click="() => inputSearch.inputRef.focus()" class='absolute right-3 h-6 text-gray-400' aria-hidden='true' />
                </div>
                <div class="mt-1 flex gap-x-4 text-xxs text-gray-500">
                    <div>Tel: <span class="font-semibold">+44 (0) 1142729165</span></div>
                    <div>Email: <span class="font-semibold">info@aw-fulfilment.co.uk</span></div>
                </div>
            </div>
        </div>

        <!-- Section: Navigation Tab -->
        <div class="w-full bg-amber-700 flex justify-between h-10 pl-3 pr-5">
            <div class="flex gap-x-2">
                <Popover v-for="navigation, idxNavigation in navigations" :key="idxNavigation" v-slot="{ open }" as="div"
                    class="relative">
                    <PopoverButton :class="open ? 'text-white' : 'text-white/90'"
                        class="group inline-flex items-center gap-x-2 rounded-md hover:bg-amber-600 px-3 py-2 text-base font-medium hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75">
                        <span>{{ navigation.label }}</span>
                        <FontAwesomeIcon v-if="navigation.subNav" icon='fal fa-chevron-down'
                            class='text-xs transition-all duration-100 ease-in-out' :class="open ? 'rotate-180' : ''"
                            aria-hidden='true' />
                    </PopoverButton>
                    <transition name="headlessui">
                        <PopoverPanel
                            class="absolute left-0 bg-amber-500 flex flex-col gap-y-0.5 rounded-md overflow-hidden z-10 mt-2 transform px-1 py-1 min-w-64">
                            <Link v-for="subNav in navigation.subNav" :href="subNav.href"
                                class="hover:bg-amber-400 px-2 py-1 rounded">
                            <FontAwesomeIcon v-if="subNav.icon" :icon='subNav.icon' class='text-sm' fixed-width
                                aria-hidden='true' />
                            {{ subNav.label }}
                            </Link>
                        </PopoverPanel>
                    </transition>
                </Popover>
            </div>
            <div class="flex gap-x-4 h-full items-center ">
                <Link href="#" class="space-x-1 text-gray-300 hover:text-white hover:-translate-y-0.5 transition-all duration-75 ease-in-out">
                    <FontAwesomeIcon fixed-width icon='fas fa-user-plus' class='opacity-80' aria-hidden='true' />
                    Register
                </Link>
                <Link href="#" class="space-x-1 text-gray-300 hover:text-white hover:-translate-y-0.5 transition-all duration-75 ease-in-out">
                    <FontAwesomeIcon fixed-width icon='fas fa-sign-in' class='opacity-80' aria-hidden='true' />
                    Login
                </Link>
            </div>
        </div>

        <!-- Section: Hero (banner) -->
        <iframe title="AW Fulfilment" style="width:100%;height: 320px; border: 0px;overflow:hidden;"
            src="https://delivery.wowsbar.com/banners/01HDRV5VBPRY0DMEED42JGFGX4" allowfullscreen></iframe>

        <!-- Section: Content -->
        <div>
            <!-- Section: Middle text -->
            <div class="text-justify">
                <h2 class="mx-auto">AW Fulfilment ensure seamless operations.</h2>
                <h3 class="mx-auto">Boost efficiency, reduce costs, and leave your customers amazed.</h3>
                <p class="text-justify">As a leading UK fulfilment center, we are committed to streamlining your supply
                    chain and ensuring seamless operations. Our state-of-the-art fulfilment warehouse UK in Sheffield's
                    Affinity Park is strategically positioned to cater to businesses across the United Kingdom. Whether
                    you're a budding e-commerce startup or an established brand, our fulfilment services are tailored to
                    meet your unique needs. Our comprehensive range of services encompasses everything you need for
                    successful order fulfilment in the UK. We offer simple tariffs, transparent systems without hidden
                    costs.</p>
                <p class="font-medium text-justify">At AW Fulfilment we offer an affordable, flexible and efficient way to
                    store your goods. Warehouse spaces available for immediate use. Your goods are stored directly within
                    our 100 000 sq foot warehouse facility. Our heavy duty racking, trained staff and onsite security setup
                    ensure your inventory is in safe hands. </p>
                <h3 class="text-center">Pay only for the space and services you use. Expand as needed.</h3>
                <p class="text-justify">Our Fulfillment as a service offers competitive prices without compromising on
                    excellence. Whether you need simple short term storage or a full service pack to store, pick & pack and
                    despatch - at AW Fulfilment Sheffield we can offer cost effective solutions. With rental rates from
                    £2.00 / pallet space per week and picking, packing and despatch services to hand so we can solve your
                    fulfilment issues.</p>
                <p class="font-medium">For European companies wishing to solve the Brexit distribution problem, we can help
                    you open up the UK market. Multilingual staff available, we speak your language: French, Spanish,
                    Italian, Czech, Slovak, German, Polish & Dutch.</p>
            </div>

            <!-- Section: Our Services -->
            <div class="py-24 sm:py-32">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Our services</h2>
                        <p class="mt-2 text-lg leading-8 text-gray-600">Learn how to grow your business with our expert
                            advice.</p>
                    </div>
                    <div
                        class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-20 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                        <article v-for="content, idxContent in contentImages" :key="idxContent"
                            class="flex flex-col items-start justify-between">
                            <div class="relative w-full">
                                <img :src="content.src" alt=""
                                    class="aspect-[16/9] w-full rounded-2xl bg-gray-100 object-cover sm:aspect-[2/1] lg:aspect-[3/2]" />
                                <div class="absolute inset-0 rounded-2xl ring-1 ring-inset ring-gray-900/10" />
                            </div>
                            <div class="max-w-xl">
                                <div class="group relative">
                                    <h3
                                        class="mt-3 text-lg font-semibold leading-6 text-gray-900 group-hover:text-gray-600">
                                        {{ content.label }}
                                    </h3>
                                    <p class="mt-1 line-clamp-3 text-sm leading-6 text-gray-600">{{ content.description }}
                                    </p>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

            <!-- Section: Customer Service -->
            <div class="relative isolate overflow-hidden bg-gray-900 px-6 py-16 sm:py-16 lg:px-8 mb-16">
                <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&crop=focalpoint&fp-y=.8&w=2830&h=1500&q=80&blend=111827&sat=-100&exp=15&blend-mode=multiply"
                    alt="" class="absolute inset-0 -z-10 h-full w-full object-cover" />
                <div class="hidden sm:absolute sm:-top-10 sm:right-1/2 sm:-z-10 sm:mr-10 sm:block sm:transform-gpu sm:blur-3xl"
                    aria-hidden="true">
                    <div class="aspect-[1097/845] w-[68.5625rem] bg-gradient-to-tr from-[#ff4694] to-[#776fff] opacity-20"
                        style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
                </div>
                <div class="absolute -top-52 left-1/2 -z-10 -translate-x-1/2 transform-gpu blur-3xl sm:top-[-28rem] sm:ml-16 sm:translate-x-0 sm:transform-gpu"
                    aria-hidden="true">
                    <div class="aspect-[1097/845] w-[68.5625rem] bg-gradient-to-tr from-[#ff4694] to-[#776fff] opacity-20"
                        style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
                </div>
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">Support center</h2>
                    <!-- <p class="mt-6 text-lg leading-8 text-gray-300">Click here</p> -->
                    <Button :style="'white'" label="Contact Us" size="xl" class="mt-10" />
                    <div class="w-3/4 flex gap-x-3 items-center mx-auto mt-7">
                        <hr class="border w-full border-gray-100/70 mt-1 rounded-full">
                        <div class="text-gray-100/70 leading-none">or</div>
                        <hr class="border w-full border-gray-100/70 mt-1 rounded-full">
                    </div>
                    <p class="mt-6 text-lg leading-8 text-gray-300">
                        <FontAwesomeIcon icon='fas fa-phone' class='' aria-hidden='true' />
                        Call us on +44 07377 188 459 <span class="font-bold">(James)</span>
                    </p>
                </div>
            </div>

            <!-- Section: Maps -->
            <div class="h-96">
                <iframe class="w-full h-full" _src="#map"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2378.920601721521!2d-1.486865683977093!3d53.39836017851476!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4879788c1041fe81%3A0xd19b7056dbeebdb0!2sAncient+Wisdom+Giftware+Wholesale!5e0!3m2!1sen!2suk!4v1555071386906!5m2!1sen!2suk"
                    framescrolling="no" marginheight="0" marginwidth="0" allowfullscreen></iframe>
            </div>
        </div>

        <footer>
            <h2 id="footer-heading" class="sr-only">Footer</h2>
            <div class="bg-gray-900 mx-auto max-w-7xl px-6 pb-8 pt-16 lg:px-8">
                <div class="xl:grid xl:grid-cols-4 xl:gap-8">
                    <!-- Contact Section -->
                    <div class="pl-3 pt-0.5 flex flex-col text-sm pb-2 space-y-3 text-gray-400">
                        <!-- Company name -->
                        <p class="text-xl text-white">Contact</p>
                        <div v-for="contact in contactAddress" class="grid grid-flow-col gap-x-2 justify-start items-start">
                            <FontAwesomeIcon fixed-width :icon="contact.icon" class="mt-1 text-gray-400" aria-hidden="true" />
                            <span class="">{{ contact.label }}</span>
                        </div>
                    </div>
                    
                    <!-- AW Family -->
                    <div class="space-y-2">
                        <p class="text-xl text-white">AW Family</p>
                        <div class="flex flex-col">
                            <Link v-for="family in families" :href="family.href" class="text-slate-400 hover:text-slate-200">
                                {{ family.label }}
                            </Link>
                        </div>
                    </div>

                    <!-- About -->
                    <div class="space-y-2">
                        <p class="text-xl text-white">About AW-Fulfillment</p>
                        <div class="flex flex-col">
                            <Link v-for="about in footerAbout" :href="about.href" class="text-slate-400 hover:text-slate-200">
                                {{ about.label }}
                            </Link>
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div class="space-y-2">
                        <p class="text-xl text-white">AW-Fulfilment</p>
                        <div class="text-justify text-gray-400">
                            AW-Fulfilment is a trading brand of Ancient Wisdom.<br>We have affordable warehouse spaces available for immediate use. There is no middle man here. Your goods are stored directly within our 100 000 sq foot state of the art warehouse facility. Our heavy duty racking, trained staff and onsite security setup ensure your inventory is in safe hands
                        </div>
                    </div>
                </div>

                <div
                    class="mt-16 border-t border-white/10 pt-8 sm:mt-20 lg:mt-24 lg:flex lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-sm font-semibold leading-6 text-white">Subscribe to our newsletter</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-300">The latest news, articles, and resources, sent
                            to your inbox weekly.</p>
                    </div>
                    <form class="mt-6 sm:flex sm:max-w-md lg:mt-0">
                        <label for="email-address" class="sr-only">Email address</label>
                        <input type="email" name="email-address" id="email-address" autocomplete="email" required
                            class="w-full min-w-0 appearance-none rounded-md border-0 bg-white/5 px-3 py-1.5 text-base text-white shadow-sm ring-1 ring-inset ring-white/10 placeholder:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:w-56 sm:text-sm sm:leading-6"
                            placeholder="Enter your email" />
                        <div class="mt-4 sm:ml-4 sm:mt-0 sm:flex-shrink-0">
                            <button type="submit"
                                class="flex w-full items-center justify-center rounded-md bg-indigo-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Section: Copyright -->
            <div class="bg-sky-500 text-white py-2 px-4 text-center">
                Copyright &#169; <span class="font-bold">2024 Aurora</span>. All rights reserved.
                <Link href="#" class="hover:underline">Terms of Use</Link> |
                <Link href="#" class="hover:underline">Privacy Policy</Link>
            </div>
        </footer>

    </div>
</template>


