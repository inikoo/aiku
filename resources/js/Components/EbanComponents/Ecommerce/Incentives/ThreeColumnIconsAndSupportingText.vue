<template>
    <div class="bg-white">
        <h2 class="sr-only">Our perks</h2>
        <div class="mx-auto max-w-7xl divide-y divide-gray-200 lg:flex lg:justify-center lg:divide-y-0 lg:divide-x lg:py-8">
            <div v-for="(perk, perkIdx) in perks" :key="perkIdx" class="py-8 lg:w-1/3 lg:flex-none lg:py-0">
                <div class="mx-auto flex max-w-xs items-center px-4 lg:max-w-none lg:px-8">
                    <component :is="perk.icon" class="h-8 w-8 flex-shrink-0 text-indigo-600" aria-hidden="true" />
                    <div class="ml-4 flex flex-auto flex-col-reverse">
                        <h3 class="font-medium text-gray-900">{{ perk.name }}</h3>
                        <p class="text-sm text-gray-500">{{ perk.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ArrowPathIcon, CalendarIcon, TruckIcon } from '@heroicons/vue/24/outline'

const perks = [
    { name: '10-year all-inclusive warranty', description: 'We’ll replace it with a new one', icon: CalendarIcon },
    { name: 'Free shipping on returns', description: 'Send it back for free', icon: ArrowPathIcon },
    { name: 'Free, contactless delivery', description: 'The shipping is on us', icon: TruckIcon },
]
</script>
