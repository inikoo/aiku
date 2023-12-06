<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCactus, faIslandTropical, faSkullCow, faFish } from '@fal'
import { faPlus } from '@far'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faPlus, faCactus, faIslandTropical, faSkullCow, faFish)
import { trans } from 'laravel-vue-i18n'

const props = defineProps<{
    data: {
        action: {
            label: string
            route: {
                name: string
                parameters: []
            }
            style: string
            tooltip: string
            type: string
        }
        description: string
        title: string
    }
}>()

const randomIcon = [
    {
        secondIcon: ['fal', 'fa-cactus'],
        firstIcon: ['fal', 'fa-skull-cow'],
    },
    {
        firstIcon: ['fal', 'fa-island-tropical'],
        secondIcon: ['fal', 'fa-fish'],
    },
]

const randomIndex = Math.floor(Math.random() * randomIcon.length)
</script>

<template>
    <div class="text-center border-gray-200 pt-14">

        <div class="mb-6">
            <FontAwesomeIcon :icon="randomIcon[randomIndex].secondIcon" class="mx-auto h-9 text-gray-200" aria-hidden="true" />
            <FontAwesomeIcon :icon="randomIcon[randomIndex].firstIcon" class="mx-7 h-12 w-12 text-gray-300" aria-hidden="true" />
            <FontAwesomeIcon :icon="randomIcon[randomIndex].secondIcon" class="mx-auto h-8  text-gray-200" aria-hidden="true" />
        </div>

        <h3 class="font-logo text-lg font-bold text-gray-800">{{ data.title ?? trans('No records found') }}</h3>
        <p v-if="data.description" class="text-sm mt-2 text-gray-500 mb-4">{{ data.description }}</p>
        <Link v-if="data.action" :href="route(data.action.route.name, data.action.route.parameters)" class="">
            <Button size="xs" :style="data.action.style"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm capitalize hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                <FontAwesomeIcon icon="far fa-plus" class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                {{ trans(data.action.tooltip) }}
            </Button>

            <!-- <Button size="xs" :type="action.style"
                class="capitalize inline-flex items-center rounded-md border text-sm font-medium shadow-sm gap-x-2">
                <FontAwesomeIcon v-if="getActionIcon(action)" :icon="getActionIcon(action)" class="" aria-hidden="true" />
                {{ getActionLabel(action) }}
            </Button> -->
        </Link>
    </div>
</template>
