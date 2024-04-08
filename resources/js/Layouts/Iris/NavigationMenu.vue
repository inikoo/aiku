<script setup lang='ts'>
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
import { Link } from '@inertiajs/vue3'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBox, faDollarSign, faHome as fasHome, faUserPlus, faSignIn } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faHome, faInfoCircle, faPhone, faFile, faChevronDown } from '@fal'

library.add(faBox, faDollarSign, fasHome, faUserPlus, faSignIn, faHome, faInfoCircle, faPhone, faFile, faChevronDown)

interface SubNav {
    name: string
    label: string
    href: string
    icon?: string
}

interface Navigation {
    [key: string]: {
        name: string
        label: string
        icon: string
        subNav?: {
            [key: string]: SubNav
        }
    }
}

const navigations: Navigation = {
    home: {
        name: 'home',
        label: 'Home',
        icon: 'fas fa-home',
        subNav: {
            homepage: {
                name: 'homepage',
                label: 'Homepage',
                href: '/',
                icon: 'fal fa-home',
            },
            about: {
                name: 'about',
                label: 'About',
                href: '/about',
                icon: 'fal fa-info-circle',
            },
            contact: {
                name: 'contact',
                label: 'Contact',
                href: '/contact',
                icon: 'fal fa-phone',
            },
            tnc: {
                name: 'tnc',
                label: 'Terms & Conditions',
                href: '/tnc',
                icon: 'fal fa-file',
            },
        }
    },
    fulfilment: {
        name: 'fulfilment',
        label: 'Fulfilment',
        icon: 'fas fa-box',
        subNav: {
            storage: {
                name: 'storage',
                label: 'Pallet storage & distribution',
                href: '/storage',
            },
            pickPack: {
                name: 'pickPack',
                label: 'Pick, Pack & dispatch',
                href: '/pick_pack',
            },
            repacking: {
                name: 'repacking',
                label: 'Repacking, reworking, etc',
                href: '/rework',
            },
        },
    },
    pricing: {
        name: 'pricing',
        label: 'Pricing',
        icon: 'fas fa-dollar-sign',
        subNav: {
            pricing: {
                name: 'pricing',
                label: 'Pricing Example',
                href: '/pricing',
            },
        }
    },
}
</script>

<template>
    <div class="w-full bg-indigo-600 flex justify-between h-10 pl-3 pr-5">
        <div class="flex gap-x-2">
            <Popover v-for="(navigation, idxNavigation) in navigations" :key="idxNavigation" v-slot="{ open }" as="div"
                class="relative">
                <PopoverButton :class="open ? 'text-white bg-indigo-500' : 'text-white/90'"
                    class="group inline-flex items-center gap-x-2 rounded-md hover:bg-indigo-500 px-3 py-2 text-base font-medium hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/75">
                    <FontAwesomeIcon :icon='navigation.icon' class='' fixed-width aria-hidden='true' />
                    <span>{{ navigation.label }}</span>
                    <FontAwesomeIcon v-if="navigation.subNav" icon='fal fa-chevron-down'
                        class='text-xs transition-all duration-100 ease-in-out' :class="open ? 'rotate-180' : ''"
                        aria-hidden='true' />
                </PopoverButton>
                <transition name="headlessui">
                    <PopoverPanel v-slot="{ close }" class="absolute left-0 bg-white ring-2 ring-indigo-500 text-gray-600 font-semibold flex flex-col gap-y-0.5 rounded-md overflow-hidden z-10 mt-2 transform p-1.5 min-w-64">
                        <Link v-for="subNav in navigation.subNav" :href="subNav.href" @click="() => close()" class="hover:bg-indigo-500 hover:text-white px-2 py-1 rounded">
                            <FontAwesomeIcon v-if="subNav.icon" :icon='subNav.icon' class='text-sm' fixed-width aria-hidden='true' />
                            {{ subNav.label }}
                        </Link>
                    </PopoverPanel>
                </transition>
            </Popover>
        </div>
        <div class="flex gap-x-4 h-full items-center ">
           <!--  <Link href="/app/login" class="space-x-1 text-gray-300 hover:text-white hover:-translate-y-0.5 transition-all duration-75 ease-in-out">
                <FontAwesomeIcon fixed-width icon='fas fa-user-plus' class='opacity-80' aria-hidden='true' />
                Register
            </Link> -->
            <a href="/app/login" class="space-x-1 text-gray-300 hover:text-white hover:-translate-y-0.5 transition-all duration-75 ease-in-out">
                <FontAwesomeIcon fixed-width icon='fas fa-sign-in' class='opacity-80' aria-hidden='true' />
                Login
            </a>
        </div>
    </div>
</template>