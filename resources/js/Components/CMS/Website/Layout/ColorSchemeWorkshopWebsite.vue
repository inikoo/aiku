<script setup lang='ts'>
import { routeType } from '@/types/route'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faExternalLink } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faExternalLink)

const props = defineProps<{
    color?: string[]
    layout?: String
    fontFamily?: string
    routeList: {
        headerRoute: routeType
        footerRoute: routeType
        webpageRoute: routeType
        notificationRoute: routeType
        menuLeftRoute: routeType
        menuRightRoute: routeType
        menuRoute: routeType
    }
}>()


</script>

<template>
    <div class="h-full rounded-md overflow-hidden border-2 border-gray-400 flex justify-center"
        :style="{ fontFamily: fontFamily }">
        <!-- Section: Notification -->
        <div :class="layout == 'fullscreen' ? 'w-full' : 'w-[80%]'" class="transition-all">

            <div
                class="group relative w-full h-8 bg-gray-200 text-gray-700 text-xxs text-center flex items-center justify-center cursor-pointer">
                <div>Notification</div>
                <div
                    class="inset-0 group-hover:bg-black/50 text-white whitespace-nowrap text-xs absolute items-center justify-center hidden group-hover:flex gap-x-1.5">
                    <span class="">Open Notification workshop</span>
                    <FontAwesomeIcon icon='fal fa-external-link' class='' fixed-width aria-hidden='true' />
                </div>
            </div>

            <!-- Section: Header  -->
            <a target="_blank"
                :href="routeList?.headerRoute?.name ? route(routeList?.headerRoute?.name, routeList?.headerRoute?.parameters) : '#'"
                class="group relative flex items-center justify-between h-20 px-4 cursor-pointer"
                :style="{ backgroundColor: color?.[0], color: color?.[1] }">
                <div
                    class="bg-gray-100 text-black border border-gray-400 px-0.5 aspect-square rounded-sm flex items-center text-[12px]">
                    Logo
                </div>

                <div class="flex justify-center gap-x-2">
                    <div class="w-[200px] bg-white rounded-sm px-2 text-[15px] text-gray-500">Search</div>
                </div>

                <div class="text-xs flex gap-x-1.5">
                    <div>Register</div>
                    <div>Login</div>
                </div>

                <div class="inset-0 bg-black/50 absolute items-center justify-center hidden group-hover:flex gap-x-1.5">
                    <span>Open Header workshop</span>
                    <FontAwesomeIcon icon='fal fa-external-link' class='' fixed-width aria-hidden='true' />
                </div>
            </a>

            <!-- Section: Menu  -->
            <a target="_blank"
                :href="routeList?.menuRoute?.name ? route(routeList?.menuRoute?.name, routeList?.menuRoute?.parameters) : '#'"
                class="group relative flex items-center justify-between h-10 px-4 cursor-pointer"
                :style="{ backgroundColor: color?.[0], color: color?.[1] }">
                <div class=" text-black  px-0.5 aspect-square rounded-sm flex items-center text-[8px]"></div>

                <div class="flex justify-center gap-x-2">
                    <div class="relative px-1 flex justify-center">
                        <div class="text-xs">Menu</div>
                        <div class="absolute -bottom-0.5 h-[2px] w-full rounded-full"
                            :style="{ backgroundColor: color?.[2] }">
                        </div>
                    </div>
                    <div class="relative px-1 flex justify-center">
                        <div class="text-xs">Product</div>
                    </div>
                    <div class="relative px-1 flex justify-center">
                        <div class="text-xs">Contact Us</div>
                        <div class="absolute -bottom-0.5 h-[2px] w-full rounded-full"
                            :style="{ backgroundColor: color?.[2] + '33' }"></div>
                    </div>
                </div>

                <div class="text-xxs flex gap-x-1.5"></div>

                <div class="inset-0 bg-black/50 absolute items-center justify-center hidden group-hover:flex gap-x-1.5">
                    <span>Open Menu workshop</span>
                    <FontAwesomeIcon icon='fal fa-external-link' class='' fixed-width aria-hidden='true' />
                </div>
            </a>

            <div class="flex justify-center">
                <!-- Section: Menu Left -->
                <!-- Uncomment if you want to include Menu Left
    <a target="_blank" 
       :href="routeList?.menuLeftRoute?.name ? route(routeList?.menuLeftRoute?.name, routeList?.menuLeftRoute?.parameters) : '#'" 
       class="group relative border-r border-gray-300 cursor-pointer flex justify-center items-center text-3xl">
        <span class="-rotate-90 whitespace-nowrap">Menu left</span>
        <div class="inset-0 bg-black/60 text-white whitespace-nowrap text-sm absolute hidden group-hover:flex items-center justify-center gap-x-1.5">
            <div class="-rotate-90 flex gap-x-1 justify-center items-center">
                <span class="block">Open Left Menu workshop</span>
                <FontAwesomeIcon icon='fal fa-external-link' class='' fixed-width aria-hidden='true' />
            </div>
        </div>
    </a>
    -->

                <a target="_blank"
                    :href="routeList?.webpageRoute?.name ? route(routeList?.webpageRoute?.name, routeList?.webpageRoute?.parameters) : '#'"
                    class="group relative col-span-5 pb-8 h-[42vh] cursor-pointer" :style="{ padding: 'auto' }">

                    <div class="mt-6 font-black mx-auto text-center text-2xl whitespace-pre-line px-4"
                        :style="{ color: color?.[0] }">
                        Lorem ipsum dolor, sit amet consectetur adipisicing elit!
                    </div>

                    <div class="mt-8 flex justify-center items-center scale-100">
                        <div class="mx-4 px-4 py-4 rounded-md w-52"
                            :style="{ color: color?.[2], border: `2px solid ${color?.[2]}` }">
                            <!-- Increased border thickness -->
                            <div class="text-sm">Basic</div> <!-- Increased font size -->
                            <div class="text-2xl font-black">$74<span class="text-xs font-normal">/month</span></div>
                            <div class="text-xs mt-2">Ideal for small businesses or startups with moderate storage
                                needs.</div>
                            <div class="mt-3 rounded w-fit px-4 text-sm"
                                :style="{ border: `2px solid ${color?.[2]}`, color: color?.[2] }">Buy</div>
                        </div>

                        <div class="mx-4 scale-110 px-4 py-4 rounded-md w-52"
                            :style="{ backgroundColor: color?.[2], color: color?.[3] }">
                            <div class="text-sm">Pro</div> <!-- Increased font size -->
                            <div class="text-2xl font-black">$279<span class="text-xs font-normal">/month</span></div>
                            <div class="text-xs mt-2">Perfect for growing businesses with increased storage demands.
                            </div>
                            <div class="mt-3 text-white rounded w-fit px-4 text-sm"
                                :style="{ backgroundColor: color?.[3], color: color?.[2] }">Buy</div>
                        </div>
                    </div>

                    <div
                        class="inset-0 bg-black/50 text-white absolute items-center justify-center hidden group-hover:flex gap-x-1.5">
                        <span>Open Webpage workshop</span>
                        <FontAwesomeIcon icon='fal fa-external-link' class='' fixed-width aria-hidden='true' />
                    </div>
                </a>

                <!-- Section: Menu Right -->
                <!-- Uncomment if you want to include Menu Right
    <a target="_blank" 
       :href="routeList?.menuRightRoute?.name ? route(routeList?.menuRightRoute?.name, routeList?.menuRightRoute?.parameters) : '#'" 
       class="group relative border-l border-gray-300 cursor-pointer flex justify-center items-center text-3xl">
        <span class="-rotate-90 whitespace-nowrap">Menu right</span>
        <div class="inset-0 bg-black/60 text-white whitespace-nowrap text-sm absolute hidden group-hover:flex items-center justify-center gap-x-1.5">
            <div class="-rotate-90 flex gap-x-1 justify-center items-center">
                <span class="block">Open Right Menu workshop</span>
                <FontAwesomeIcon icon='fal fa-external-link' class='' fixed-width aria-hidden='true' />
            </div>
        </div>
    </a>
    -->
            </div>


            <!-- Section: Header  -->
            <a target="_blank"
                :href="routeList?.footerRoute?.name ? route(routeList?.footerRoute?.name, routeList?.footerRoute?.parameters) : '#'"
                class="group relative flex items-center justify-between cursor-pointer"
                :style="{ backgroundColor: color?.[2], color: color?.[3] }">
                <div class="w-full h-full grid grid-cols-4 px-6 py-3 text-xxs">
                    <div class="flex flex-col">
                        <div>
                            Home
                        </div>
                        <div>
                            About
                        </div>
                        <div>
                            Contact us
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <div>
                            Terms of use
                        </div>
                        <div>
                            Privacy Policy
                        </div>
                    </div>

                    <div class="flex flex-col text-justify text-[8px]">
                        Lorem ipsum dolor sit amet consectetur adipisicing elit. Aut, placeat!
                    </div>

                    <div class="h-fit flex justify-end">
                        <div
                            class="bg-gray-100 text-black border border-gray-400 px-0.5 aspect-square rounded-sm flex items-center text-[14px]">
                            Logo
                        </div>
                    </div>

                </div>


                <div
                    class="inset-0 bg-black/50 text-white absolute items-center justify-center hidden group-hover:flex gap-x-1.5">
                    <span>Open Footer workshop</span>
                    <FontAwesomeIcon icon='fal fa-external-link' class='' fixed-width aria-hidden='true' />
                </div>
            </a>
        </div>




    </div>
</template>
