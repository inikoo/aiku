<script setup lang='ts'>
import { faEnvelope, faEnvelopeOpenText } from "@fal"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { useFormatTime } from '@/Composables/useFormatTime'
import { Link } from "@inertiajs/vue3"
import { inject } from "vue"
import { layoutStructure } from "@/Composables/useLayoutStructure"
import Profile from "@/Pages/Grp/Profile.vue"

library.add(faEnvelope, faEnvelopeOpenText)

const props = defineProps<{
    close: Function
}>()

const layout = inject('layout', layoutStructure)
// console.log('ewew', layout.user.notifications)
</script>

<template>
    <div class="flex items-center flex-col w-full overflow-auto min-h-11 max-h-96">
        <ul v-if="layout.user.notifications.length" role="list" class="w-full divide-y divide-gray-100 overflow-y-auto">
            <li v-for="notif in layout.user.notifications" :key="notif.id"
                class="relative flex justify-between gap-x-6 px-1 py-2 hover:bg-gray-50 sm:px-2">
                <font-awesome-icon :icon="notif.read ? ['fal', 'envelope-open-text'] : ['fal', 'envelope']"
                    :class="['h-8 w-8 flex-none m-auto', notif.read && 'text-gray-400']" />
                <div class="min-w-0 flex-auto relative">
                    <div class="text-sm font-semibold leading-6" :class="[notif.read ? 'text-gray-400' : '']">
                        <component :is="notif.href ? Link : 'div'" :href="notif.href">
                            <span class="absolute inset-x-0 -top-px bottom-0"></span>
                            {{ notif.title }}
                        </component>
                    </div>
                    <span class="text-[10px] text-gray-500 absolute top-0 right-0 mt-1 mr-1">
                        {{ useFormatTime(notif.created_at) }}
                    </span>
                    <p :class="['mt-1 flex text-xs leading-5 truncate', notif.read ? 'text-gray-400' : 'text-gray-500']">
                        {{ notif.body }}
                    </p>
                </div>
            </li>
        </ul>

        <div v-else class="mx-auto italic text-gray-400">
            You have no new notifications.
        </div>

        <div class="flex w-full justify-center border-t border-gray-200 mt-3 pt-3">
            <div @click="() => (close(), layout.stackedComponents.push({ component: Profile, data: { currentTab: 'notifications' }}))" class="cursor-pointer px-2 text-gray-400 hover:text-gray-500 font-semibold">
                Show all notification
            </div>
        </div>
    </div>
</template>
