<script setup lang="ts">
import { useLocaleStore } from "@/Stores/locale"
import { useLayoutStore } from "@/Stores/layout"

import { useDatabaseList } from "vuefire"
import { getDatabase, ref as dbRef } from "firebase/database"
import { initializeApp } from "firebase/app"
import serviceAccount from "@/../private/firebase/aiku-firebase.json"
const firebaseApp = initializeApp(serviceAccount);
const db = getDatabase(firebaseApp)
const activities = useDatabaseList(dbRef(db, 'aw'))

type UserOnline = {
    id: string
    is_active: boolean
    last_active: string
    route: object
    user: {
        avatar_id: number
        contact_name: string
        username: string
    }
}
const locale = useLocaleStore()
const layout = useLayoutStore()

</script>

<template>
    <div class="bg-indigo-100/50 text-xs h-full border-l border-gray-200 space-y-4">
        <TransitionGroup name="list" tag="ul">
            <!-- Online Users -->
            <li class="text-white" v-if="layout.rightSidebar.activeUsers" key="1">
                <div class="pl-2.5 pr-1.5 py-1 bg-indigo-500 flex items-center leading-none">
                    <div>Active Users</div>
                </div>
                <div v-for="(option, index) in activities"
                    class="pl-2.5 pr-1.5 flex justify-start items-center py-1 gap-x-2.5 cursor-default">
                    <img :src="`/media/group/${option.user.avatar_id}`" :alt="option.user.contact_name" srcset=""
                        class="h-5 rounded-full shadow ring-1 ring-gray-100">
                    <p class="text-gray-100 flex flex-col gap-y-0.5">
                        <span class="font-semibold text-gray-600 leading-none">{{ option.user.username }}</span>
                        <span class="capitalize text-gray-500 whitespace-normal leading-none text-[10px]">{{ option.route.module }}</span>
                    </p>
                </div>
            </li>
            <!-- Language -->
            <li class="text-white space-y-1" v-if="layout.rightSidebar.language" key="2">
                <div class="pl-2.5 pr-1.5 py-1 bg-indigo-500 flex items-center leading-none">
                    <div>Language</div>
                </div>
                <div class="text-gray-600 pl-2.5 pr-1.5">
                    <span class="uppercase font-semibold">({{ locale.language.code }})</span>
                    {{ locale.language.name }}
                </div>
            </li>
        </TransitionGroup>

    </div>
</template>

<style>
.list-move,
.list-enter-active,
.list-leave-active {
    transition: all 0.4s ease-in-out;
}

.list-enter-from,
.list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}

.list-leave-active {
    position: absolute;
}
</style>