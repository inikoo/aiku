<script setup lang='ts'>
import { Link } from '@inertiajs/vue3'
import Copyright from '@/Layouts/Iris/Copyright.vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBuilding, faEnvelope, faBalanceScale, faIndustry, faPhone } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faBuilding, faEnvelope, faBalanceScale, faIndustry, faPhone)

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
        href: "/contact",
    },
    {
        label: "About Us",
        href: "/about",
    },
    {
        label: "Delivery",
        href: "/shipping",
    },
    {
        label: "Terms & Conditions",
        href: "/tnc",
    }
]
</script>

<template>
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
                    <div class="flex flex-col gap-y-1">
                        <Link v-for="family in families" :href="family.href" class="text-slate-400 hover:text-slate-200">
                            {{ family.label }}
                        </Link>
                    </div>
                </div>
                <!-- About -->
                <div class="space-y-2">
                    <p class="text-xl text-white">About AW-Fulfilment</p>
                    <div class="flex flex-col gap-y-1">
                        <Link v-for="about in footerAbout" :href="about.href" class="text-slate-400 hover:text-slate-200">
                            {{ about.label }}
                        </Link>
                    </div>
                </div>
                <!-- Description -->
                <div class="space-y-2">
                    <p class="text-xl text-white">AW-Fulfilment</p>
                    <div class="text-justify text-gray-400 text-sm">
                        AW-Fulfilment is a trading brand of Ancient Wisdom.<br>We have affordable warehouse spaces available for
                        immediate use. There is no middle man here. Your goods are stored directly within our 100 000 sq foot
                        state of the art warehouse facility. Our heavy duty racking, trained staff and onsite security setup
                        ensure your inventory is in safe hands
                    </div>
                </div>
            </div>
            <div class="mt-16 border-t border-white/10 pt-8 sm:mt-20 lg:mt-24 lg:flex lg:items-center lg:justify-between">
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
        <Copyright />
    </footer>
</template>
