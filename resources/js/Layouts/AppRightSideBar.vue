<template>
    <div class="bg-indigo-100/50 text-xs h-full border-l border-gray-200 space-y-2">

        <!-- Online Users -->
        <section class="text-white">
            <div class="pl-2.5 pr-1.5 py-0.5 bg-indigo-500 flex items-center">
                <!-- <div class="ring-1 h-2 aspect-square rounded-full" :class="[activities.length > 0 ? 'bg-green-400 ring-green-600' : 'bg-gray-400 ring-gray-600']" /> -->
                <div>Active Users</div>
            </div>
            <FooterTab >
                <template #default>
                    <div v-for="(option, index) in activities"
                        class=" pl-2.5 pr-1.5 flex justify-start items-center py-1 gap-x-2.5 cursor-default">
                        <img :src="`/media/group/${option.user.avatar_id}`" :alt="option.user.contact_name" srcset=""
                            class="h-5 rounded-full shadow ring-1 ring-gray-100">
                        <p class="text-gray-100 flex flex-col gap-y-0.5">
                            <span class="font-semibold text-gray-600 leading-none">{{ option.user.username }}</span>
                            <span class="capitalize text-gray-500 whitespace-normal leading-none text-[10px]">{{ option.route.module }}</span>
                        </p>
                    </div>
                </template>
            </FooterTab>
        </section>

        <!-- Language -->
        <!-- <section class="text-white py-2">
            <div class="pl-2.5 pr-1.5 py-0.5 bg-indigo-500">Language</div>
        </section> -->
    </div>
</template>

<script setup lang="ts">
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

console.log(activities)
</script>
