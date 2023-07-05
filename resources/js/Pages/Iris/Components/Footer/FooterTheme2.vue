<!--
  This example requires some changes to your config:
  
  ```
  // tailwind.config.js
  module.exports = {
    // ...
    plugins: [
      // ...
      require('@tailwindcss/forms'),
    ],
  }
  ```
-->
<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faFacebook, faInstagram, faTwitter, faGithub, faYoutube } from "@fortawesome/free-brands-svg-icons"
import { faMapMarkerAlt, faEnvelope, faBalanceScale, faBuilding, faPhone, faMap } from "@fortawesome/free-solid-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faFacebook, faInstagram, faTwitter, faGithub, faYoutube, faMapMarkerAlt, faEnvelope, faBalanceScale, faBuilding, faPhone, faMap)


const navigations = [
    {
        title: 'solutions',
        data: [
            { name: 'Marketing', href: '#' },
            { name: 'Analytics', href: '#' },
            { name: 'Commerce', href: '#' },
            { name: 'Insights', href: '#' },
        ],
    },
    {
        title: 'support',
        data: [
            { name: 'Pricing', href: '#' },
            { name: 'Documentation', href: '#' },
            { name: 'Guides', href: '#' },
            { name: 'API Status', href: '#' },
        ],
    },
    {
        title: 'company',
        data: [
            { name: 'About', href: '#' },
            { name: 'Blog', href: '#' },
            { name: 'Jobs', href: '#' },
            { name: 'Press', href: '#' },
            { name: 'Partners', href: '#' },
        ],
    },
    {
        title: 'legal',
        data: [
            { name: 'Claim', href: '#' },
            { name: 'Privacy', href: '#' },
            { name: 'Terms', href: '#' },
        ],
    },
]

const socials = [
    {
        title: "Facebook",
        href: '#facebook',
        icon: ['fab', 'fa-facebook'],
    },
    {
        title: "Instagram",
        href: '#instagram',
        icon: ['fab', 'fa-instagram'],
    },
    {
        title: "Twitter",
        href: '#twitter',
        icon: ['fab', 'fa-twitter'],
    },
    {
        title: "Github",
        href: '#github',
        icon: ['fab', 'fa-github'],
    },
]

const addresses = [
    {
        title: 'location',
        value: 'Ancient Wisdom s.r.o., CTPark Trnava, Prílohy, 919 26 Zavar, Slovakia',
        icon: 'fas fa-map-marker-alt',
    },
    {
        title: 'billingAddress',
        value: 'Billing Address: Zavarska 10/G,  917 01 Trnava, Slovakia',
        icon: 'fas fa-map',
    },
    {
        title: 'vat',
        value: 'VAT: SK2120525440',
        icon: 'fas fa-balance-scale',
    },
    {
        title: 'building',
        value: 'Reg: 50920600',
        icon: 'fas fa-building',
    },
    {
        title: 'phone',
        value: '+421 (0)33 558 60 71',
        icon: 'fas fa-phone',
    },
    {
        title: 'email',
        value: '<a href=mailto:contact@awgifts.eu>contact@awgifts.eu</a>',
        icon: 'fas fa-envelope',
    },
]
</script>

<template>
    <footer class="bg-gray-50 px-6" aria-labelledby="footer-heading">
        <h2 id="footer-heading" class="sr-only">Footer</h2>
        <div class="mx-auto max-w-7xl px-6 pb-8 pt-20 sm:pt-24 lg:px-8 lg:pt-12">
            <div class="xl:grid xl:grid-cols-3 xl:gap-x-24 xl:px-6">
                <!-- Group: Contact Information -->
                <div class="mt-12 xl:mt-0 space-y-6">
                    <h3 class="text-sm font-bold leading-6 text-gray-700">Contact Information</h3>
                    <div class="flex flex-col gap-y-2.5">
                        <div v-for="address in addresses" class="grid grid-cols-[auto,1fr] gap-4 items-center justify-start gap-x-3">
                            <div class="w-5 flex items-center justify-center text-gray-400">
                                <FontAwesomeIcon :icon="address.icon" :title="address.title" aria-hidden="true" />
                            </div>
                            <span class="leading-5 text-gray-600" v-html="address.value"></span>
                        </div>
                    </div>
                </div>

                <!-- Group: Navigations -->
                <div class="flex gap-8 xl:col-span-2">
                    <div class="flex grow justify-between">
                        <!-- Navigations -->
                        <div v-for="navigation in navigations" class="space-y-6">
                            <h3 class="text-sm font-bold leading-6 text-gray-700 capitalize">{{ navigation.title }}</h3>
                            <ul role="list" class="space-y-3">
                                <li v-for="item in navigation.data" :key="item.name">
                                    <a :href="item.href" class="text-sm leading-6 text-gray-600 hover:text-indigo-500">
                                        {{ item.name }}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Social Media -->
            <div class="border-t border-gray-900/10 pt-8 sm:mt-10 flex flex-col md:flex-row items-center justify-between mt-16 lg:mt-18 xl:px-3">
                <div class="flex space-x-6 md:order-2">
                    <a v-for="social in socials" :key="social.name" :href="social.href"
                        class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">{{ social.title }}</span>
                        <FontAwesomeIcon :icon="social.icon" class="h-6 w-6" aria-hidden="true" />
                    </a>
                </div>
                <p class="mt-4 text-xs leading-5 text-gray-500 md:order-1 md:mt-0">
                    &copy; 2023 <span class="font-bold">AW Advantage</span>, Inc. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</template>