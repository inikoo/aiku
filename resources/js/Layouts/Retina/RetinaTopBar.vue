<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
// import { useLayoutStore } from "@/Stores/retinaLayout"
import { Disclosure } from "@headlessui/vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { trans } from "laravel-vue-i18n"
import Image from "@/Components/Image.vue"

import { faChevronDown } from "@far"
import { faDotCircle } from "@fas"
import { faTerminal, faCog } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"

import MenuTopRight from "@/Layouts/Retina/MenuTopRight.vue"
import { inject } from "vue"
import ScreenWarning from "@/Components/Utils/ScreenWarning.vue"

library.add(faChevronDown, faDotCircle, faTerminal, faCog)

const props = defineProps<{
    sidebarOpen: boolean
    logoRoute: string
    urlPrefix: string
}>()

defineEmits<{
    (e: "sidebarOpen", value: boolean): void
}>()

const layout = inject('layout')

// For label
// const label = {
//     organisationSelect: trans("Select organisation"),
//     shopSelect: trans("Go to shop"),
//     warehouseSelect: trans("Select warehouses"),
//     fulfilmentSelect: trans("Select fulfilments")
// }

const isStaging = layout.app.environment === 'staging'
console.log('environment', isStaging)

</script>

<template>
    <Disclosure as="nav" class="fixed z-[21] w-full " v-slot="{ open }"
                :style="{
            'color': layout.app.theme[2]
        }"
        :class="isStaging ? 'top-4' : 'top-0'"
    >
        <ScreenWarning v-if="isStaging" />
    
        <div class="mt-1 flex h-11 lg:h-10 flex-shrink-0 gap-x-2">
            <div class="flex">
                <!-- Mobile: Hamburger -->
                <button class="block md:hidden w-10 h-10 relative focus:outline-none" @click="$emit('sidebarOpen', !sidebarOpen)">
                    <span class="sr-only">Open sidebar</span>
                    <div class="block w-5 absolute left-1/2 top-1/2   transform  -translate-x-1/2 -translate-y-1/2">
                        <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-900 transform transition duration-200 ease-in-out"
                              :class="{'rotate-45': sidebarOpen,' -translate-y-1.5': !sidebarOpen }"></span>
                        <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-900 transform transition duration-100 ease-in-out" :class="{'opacity-0': sidebarOpen } "></span>
                        <span aria-hidden="true" class="block absolute rounded-full h-0.5 w-5 bg-gray-900 transform transition duration-200 ease-in-out"
                              :class="{'-rotate-45': sidebarOpen, ' translate-y-1.5': !sidebarOpen}"></span>
                    </div>
                </button>

                <!-- App Title: Image and Title -->
                <div class="ml-3 w-0 md:w-44 mt-1 flex flex-1 items-center justify-center md:justify-start transition-all duration-300 ease-in-out"
                     :class="[
                        // layout.leftSidebar.show ? 'md:w-44 md:pr-4' : 'md:w-12'
                    ]"
                     :style="{
                        // 'background-color': layout.app.theme[0],
                        'color': layout.app.theme[2],
                        // 'border-bottom': `1px solid ${layout.app.theme[1]}3F`
                    }"
                >
                    <Link :href="layout.app?.url ?? '#'"
                          class="pl-2 hidden md:flex flex-nowrap items-center h-full overflow-hidden gap-x-1.5 transition-all duration-200 ease-in-out"
                          :class="[
                            // layout.leftSidebar.show ? 'py-1 pl-4' : 'pl-3 w-full'
                        ]"
                    >
                        <!-- <Image :src="undefined" class="aspect-square h-6" /> -->
                        <Transition name="slide-to-left">
                            <p v-if="layout.leftSidebar.show || true" class="capitalize text-lg bg-clip-text font-bold whitespace-nowrap leading-none lg:truncate"
                               :style="{color: layout.app.theme[2]}"
                            >
                                {{ layout.website?.label }}
                            </p>
                        </Transition>
                    </Link>
                </div>
            </div>

            <div class="flex items-center w-full justify-between pr-6 space-x-3">
                <!-- Section: Subsections (Something will teleport to this section) -->
                <div class="flex h-full" id="RetinaTopBarSubsections">
                </div>

                <!-- Section: Search, Notification, Profile -->
                <MenuTopRight :urlPrefix="urlPrefix" />
            </div>
        </div>
    </Disclosure>
</template>

