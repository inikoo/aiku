<script setup lang='ts'>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faBuilding, faEnvelope, faBalanceScale, faIndustry, faPhone } from '@fas'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faBuilding, faEnvelope, faBalanceScale, faIndustry, faPhone)

const contactAddress = {
    company: {
        label: "Ancient Wisdom Deals Ltd. Affinity Park, Europa Drive   Sheffield, s9 1xt",
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
</script>

<template>
    <div class="relative isolate bg-white">
        <div class="h-full mx-auto grid grid-cols-1 lg:grid-cols-2 py-24">
            <div class=" static flex">
                <div class="absolute inset-y-0 left-0 -z-10 w-full overflow-hidden bg-gray-100 ring-1 ring-gray-900/10 lg:w-1/2">
                    <svg class="absolute inset-0 h-full w-full stroke-gray-200 [mask-image:radial-gradient(100%_100%_at_top_right,white,transparent)]"
                        aria-hidden="true">
                        <defs>
                            <pattern id="83fd4e5a-9d52-42fc-97b6-718e5d7ee527" width="200" height="200" x="100%" y="-1"
                                patternUnits="userSpaceOnUse">
                                <path d="M130 200V.5M.5 .5H200" fill="none" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" stroke-width="0" fill="white" />
                        <svg x="100%" y="-1" class="overflow-visible fill-gray-50">
                            <path d="M-470.5 0h201v201h-201Z" stroke-width="0" />
                        </svg>
                        <rect width="100%" height="100%" stroke-width="0"
                            fill="url(#83fd4e5a-9d52-42fc-97b6-718e5d7ee527)" />
                    </svg>
                </div>

                <div class="justify-self-center self-center px-10">
                    <img src="https://www.aw-fulfilment.co.uk/wi.php?id=1830524" alt="">
                    <p class="mt-6 text-lg text-gray-500">
                        Feel free to contact us at any time via the telephone or email details on the right.
                        For the dynamic go-getters who like to make things happen right now.. hit the register link (and I'll get right back to you) or call me, James on 0737 7188457  Or WhatsApp me HERE.
                    </p>
                </div>
            </div>

            <div class="px-6 self-center ">
                <!-- Contact Section -->
                <div class="pl-6 pt-0.5 flex flex-col text-lg pb-2 space-y-3 text-gray-500">
                    <h2 class="text-4xl font-bold tracking-tight text-gray-600 mb-5">Get in touch</h2>
                    <div v-for="contact in contactAddress" class="grid grid-flow-col gap-x-2 justify-start items-start">
                        <FontAwesomeIcon fixed-width :icon="contact.icon" class="mt-1 text-gray-400" aria-hidden="true" />
                        <span class="">{{ contact.label }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
