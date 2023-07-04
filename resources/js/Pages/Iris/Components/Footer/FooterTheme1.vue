<script setup lang="ts">
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faFacebook, faInstagram, faTwitter, faGithub, faYoutube } from "@fortawesome/free-brands-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faFacebook, faInstagram, faTwitter, faGithub, faYoutube)


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
        icon: ['fab', 'fa-facebook'],
    },
    {
        title: "Instagram",
        icon: ['fab', 'fa-instagram'],
    },
    {
        title: "Twitter",
        icon: ['fab', 'fa-twitter'],
    },
    {
        title: "Github",
        icon: ['fab', 'fa-github'],
    },
    {
        title: "Youtube",
        icon: ['fab', 'fa-youtube'],
    },
]
</script>

<template>
    <div class="arya">
    <footer class="bg-gray-900" aria-labelledby="footer-heading">
        <h2 id="footer-heading" class="sr-only">Footer</h2>
        <div class="mx-auto max-w-7xl px-6 pb-8 pt-16 sm:pt-24 lg:px-8 lg:pt-32">
            <div class="xl:grid xl:grid-cols-3 xl:gap-20 items-center">

                <!-- Box -->
                <div class="grid justify-center space-y-5 rounded-xl bg-gray-950 border border-indigo-500 py-4 mb-8 xl:mb-0">
                    <div class=" flex justify-center">
                        <img class="h-24" src="https://tailwindui.com/img/logos/mark.svg?color=indigo&shade=500"
                            alt="Company name" />
                    </div>
                    <p class="px-3 text-center text-sm text-gray-300">
                        Making the world a better place through constructing elegant hierarchies.
                    </p>
                    <div class="flex justify-center">
                        <div class="text-gray-100 border-t border-gray-500 w-10/12" />
                    </div>
                    <div class="flex justify-center space-x-6">
                        <a v-for="(social, index) in socials" :key="index" href="#"
                            class="text-gray-500 py-0.5 px-1.5  flex items-center justify-center hover:text-gray-400 cursor-pointer">
                            <span class="sr-only">{{ social.title }}</span>
                            <FontAwesomeIcon :icon="social.icon" class="h-6 w-6" aria-hidden="true" />
                        </a>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex gap-x-10 justify-between">
                        <div v-for="navigation in navigations" class="space-y-2">
                            <h3 class="font-bold text-white capitalize">{{navigation.title}}</h3>
                            <ul role="list" class="space-y-1.5">
                                <li v-for="item in navigation.data" :key="item.name">
                                    <a :href="item.href" class="text-sm text-gray-300 hover:text-white">
                                        {{ item.name }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-16 border-t border-white/10 pt-8 sm:mt-20 lg:mt-24 text-center">
                <p class="text-xs leading-5 text-gray-400">&copy; 2023 AW Advantage, Inc. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>
</template>

<style>
.arya{
    width: 100%;
}
</style>