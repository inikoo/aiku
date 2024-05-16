<script setup lang='ts'>
import { faEnvelope, faEnvelopeOpenText } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { useFormatTime } from '@/Composables/useFormatTime'
import { routeType } from "@/types/route"
import { Link } from "@inertiajs/vue3"

library.add(faEnvelope, faEnvelopeOpenText)

const props = defineProps<{
    messages: {
        data: {
            id: number
            read: boolean
            route: routeType
            title: string
            body: string
            created_at: Date | string
        }[]
    }
}>()

</script>

<template>
    <div class="flex items-center w-full overflow-auto min-h-11 max-h-96">
        <ul v-if="messages.data.length" role="list" class="w-full divide-y divide-gray-100 overflow-hidden">
            <li v-for="message in messages.data" :key="message.id"
                class="relative flex justify-between gap-x-6 px-1 py-2 hover:bg-gray-50 sm:px-2">
                    <font-awesome-icon :icon="message.read ? ['fal', 'envelope-open-text'] : ['fal', 'envelope']"
                        :class="['h-8 w-8 flex-none m-auto', message.read && 'text-gray-400']" />
                    <div class="min-w-0 flex-auto relative">
                        <div
                            :class="['text-sm font-semibold leading-6', message.read ? 'text-gray-400' : '']">
                            <component :is="message.route?.name ? Link : 'div'" :href="message.route?.name ? route(message.route.name, message.route?.parameters) : '#'">
                                <span :class="['absolute inset-x-0 -top-px bottom-0']"></span>
                                {{ message.title }}
                            </component>
                        </div>
                        <span
                            class="text-[10px] text-gray-500 absolute top-0 right-0 mt-1 mr-1">{{ useFormatTime(message.created_at) }}</span>
                        <p :class="['mt-1 flex text-xs leading-5', message.read ? 'text-gray-400' : 'text-gray-500']">
                            <span :name="message.body" class="relative truncate hover:underline">{{ message.body
                                }}</span>
                        </p>
                    </div>
            </li>
        </ul>

        <div v-else class="mx-auto italic text-gray-500">
            You have no notifications.
        </div>
    </div>
</template>
