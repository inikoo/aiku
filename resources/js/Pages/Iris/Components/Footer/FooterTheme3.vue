<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faFacebook, faInstagram, faTwitter, faGithub, faYoutube } from "@fortawesome/free-brands-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faFacebook, faInstagram, faTwitter, faGithub, faYoutube)


const navigations = [
    {
        title: 'About',
        href: '#',
    },
    {
        title: 'Blog',
        href: '#',
    },
    {
        title: 'Jobs',
        href: '#',
    },
    {
        title: 'Press',
        href: '#',
    },
    {
        title: 'Accessibility',
        href: '#',
    },
    {
        title: 'Partners',
        href: '#',
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
    {
        title: "Youtube",
        href: '#youtube',
        icon: ['fab', 'fa-youtube'],
    },
]
</script>

<template>
    <footer class="bg-slate-800">
        <div class="mx-auto max-w-7xl overflow-hidden px-6 py-20 sm:py-24 lg:px-8">
            <!-- Navigations -->
            <nav class="grid grid-cols-2 sm:flex sm:justify-center sm:space-x-12" aria-label="Footer">
                <div v-for="navigation in navigations" :key="navigation.title" class="flex justify-center sm:inline pb-6">
                    <a :href="navigation.href" class="px-4 sm:px-0 text-sm leading-6 text-gray-100 hover:text-gray-400">{{ navigation.title }}</a>
                </div>
            </nav>

            <!-- Social Media -->
            <div class="mt-10 flex justify-center space-x-10">
                <a v-for="social in socials" :key="social.name" :href="social.href"
                    class="text-gray-400 hover:text-gray-100">
                    <span class="sr-only">{{ social.title }}</span>
                    <FontAwesomeIcon :icon="social.icon" class="h-6 w-6" aria-hidden="true" />
                </a>
            </div>
            <p class="mt-10 text-center text-xs leading-5 text-gray-500">&copy; 2023 <span class="font-extrabold">AW Advantage</span>, Inc. All rights reserved.
            </p>
        </div>
    </footer>
</template>