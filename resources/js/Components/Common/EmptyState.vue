<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCactus, faIslandTropical, faSkullCow, faFish } from "@/../private/pro-light-svg-icons"
import { faPlus } from "@/../private/pro-regular-svg-icons"
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

        <div>
            <FontAwesomeIcon :icon="randomIcon[randomIndex].secondIcon" class="mx-auto h-9 text-gray-400" aria-hidden="true" />
            <FontAwesomeIcon :icon="randomIcon[randomIndex].firstIcon" class="mx-7 h-12 w-12 text-gray-500" aria-hidden="true" />
            <FontAwesomeIcon :icon="randomIcon[randomIndex].secondIcon" class="mx-auto h-8  text-gray-400" aria-hidden="true" />
        </div>

        <h3 class="mt-3 font-semibold text-gray-800 capitalize">{{ trans(data.title) ?? trans('No records') }}</h3>
        <p class="text-sm text-gray-500 mb-4">{{ trans(data.description) }}</p>
        <Link :href="route(data.action.route.name, data.action.route.parameters)" class="">
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
