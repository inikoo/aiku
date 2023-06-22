<script setup lang="ts">
import { ref } from 'vue'
import FooterTab from '@/Components/Footer/FooterTab.vue'
import { useLocaleStore } from "@/Stores/locale"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircle } from "@/../private/pro-solid-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faCircle)


import { useDatabaseList, useDatabaseObject } from "vuefire"
import { getDatabase, ref as dbRef } from "firebase/database"
import { initializeApp } from "firebase/app"
import serviceAccount from "@/../private/firebase/aiku-firebase.json"
const firebaseApp = initializeApp(serviceAccount);
const db = getDatabase(firebaseApp)
const activities = useDatabaseList(dbRef(db, 'aw'))

const locale = useLocaleStore()
const isTabActive = ref(false)

</script>

<template>
    <footer class="z-20 fixed w-screen bg-gray-800 bottom-0 right-0  text-white">
        <!-- Helper: Outer background (close popup purpose) -->
        <div class="fixed z-40 right-0 top-0 bg-transparent w-screen h-screen" @click="isTabActive = !isTabActive"
            :class="[isTabActive ? '' : 'hidden']"></div>
        <div class="flex justify-between">
            <!-- Left Section -->
            <div class="pl-4 flex items-center gap-x-1.5">
                <img src="@/../art/favicons/favicon-purple-16x16.png" alt="" class="h-3.5 aspect-square">
                <!-- <img src="/art/logo-color-trimmed.png" alt="" class="h-4"> -->
                <span class="text-purple-400 font-semibold">aiku</span>
            </div>

            <!-- Right Section -->
            <div class="flex items-end flex-row-reverse text-sm">
                <!-- Tab: Active Users -->
                <div class="relative h-full flex z-50 select-none justify-center items-center px-8 gap-x-1 cursor-pointer"
                    :class="[isTabActive == 'activeUsers' ? 'bg-gray-600' : 'bg-gray-800']"
                    @click="isTabActive == 'activeUsers' ? isTabActive = !isTabActive : isTabActive = 'activeUsers'"
                >
                    <div class="text-xs text-gray-300 flex items-center gap-x-1">
                        <div class="ring-1 h-2 aspect-square rounded-full" :class="[activities.length > 0 ? 'bg-green-400 ring-green-600' : 'bg-gray-400 ring-gray-600']" />
                        Active Users ({{ activities.length }})
                    </div>
                    <FooterTab v-if="isTabActive == 'activeUsers'">
                        <template #default>
                            <div v-for="(option, index) in activities" class="flex justify-start py-1 px-2 gap-x-1.5 hover:bg-gray-700 cursor-default">
                                <img :src="`/media/group/${option.user.avatar_id}`" :alt="option.user.contact_name" srcset="" class="h-4 rounded-full shadow">
                                <p class="text-left text-gray-100">
                                    <!-- <span class="font-semibold">{{ option.user.contact_name }}</span>  -->
                                    <span class="font-semibold text-gray-100">{{ option.user.username }}</span> - 
                                    <span class="capitalize text-gray-300">{{ option.route.module }}</span>
                                </p>
                            </div>
                        </template>
                    </FooterTab>
                </div>

                <!-- Tab: Language -->
                <div class="relative h-full flex z-50 select-none justify-center items-center px-8 cursor-pointer"
                    :class="[isTabActive == 'language' ? 'bg-gray-600' : 'bg-gray-800']"
                    @click="isTabActive == 'language' ? isTabActive = !isTabActive : isTabActive = 'language'"
                >
                    <FontAwesomeIcon icon="fal fa-language" class="text-xs mr-1 h-5 text-gray-300"></FontAwesomeIcon>
                    <div class="h-full font-extralight text-xs flex items-center leading-none text-gray-300">{{ locale.language.code }}</div>
                    <FooterTab v-if="isTabActive === 'language'">
                        <template #default>
                            <div v-for="(option, index) in locale.languageOptions" :class="[ locale.language.id == index ? 'bg-gray-600 hover:bg-gray-500' : '', 'grid hover:bg-gray-700 py-1.5']"
                                @click="locale.language.id = Number(index), locale.language.name = option.label"
                            >
                                {{ option.label }}
                            </div>
                        </template>
                    </FooterTab>
                </div>
            </div>

            
        </div>
    </footer>
</template>



