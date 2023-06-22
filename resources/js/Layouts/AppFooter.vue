<script setup lang="ts">
import { ref } from 'vue'
import FooterTabLanguage from '@/Components/Footer/FooterTabLanguage.vue'
import FooterTabActiveUsers from '@/Components/Footer/FooterTabActiveUsers.vue'
import { useLocaleStore } from "@/Stores/locale"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCircle } from "@/../private/pro-solid-svg-icons"
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faCircle)


import { useDatabaseList, useDatabaseObject } from "vuefire"
import { getDatabase, ref as dbRef } from "firebase/database"
import { initializeApp } from "firebase/app"
import serviceAccount from "/home/aiku/aiku/storage/app/aiku-firebase.json";
const firebaseApp = initializeApp(serviceAccount);
const db = getDatabase(firebaseApp)
const activities = useDatabaseList(dbRef(db, 'aw'))

const locale = useLocaleStore()
const isTabActive = ref(false)

</script>

<template>
    <footer class="z-20 fixed w-screen bg-gray-800 bottom-0 right-0  text-white grid justify-end">
        <!-- Helper: Outer background (close popup purpose) -->
        <div class="fixed z-40 right-0 top-0 bg-gray-800/30 w-screen h-screen" @click="isTabActive = !isTabActive"
            :class="[isTabActive ? '' : 'hidden']"></div>
        <div class="flex items-end flex-row-reverse text-sm">
            <!-- Tab: Language -->
            <div class="relative flex z-50 select-none justify-center px-8  cursor-pointer"
                :class="[isTabActive == 'language' ? 'bg-gray-600' : 'bg-gray-800 hover:bg-gray-700']"
                @click="isTabActive == 'language' ? isTabActive = !isTabActive : isTabActive = 'language'"
            >
                <FontAwesomeIcon icon="fal fa-language" class="mr-1 h-5 text-gray-400"></FontAwesomeIcon>
                <span class="font-thin text-sm">{{ locale.language.code }}</span>
                
                <!-- The popup -->
                <div class="absolute bottom-5 right-0 w-40 min-w-min overflow-hidden rounded-t"
                    :class="[isTabActive == 'language' ? 'h-max' : 'h-0']"
                >
                    <FooterTabLanguage :active="isTabActive == 'language'" :data="locale.languageOptions" :selected="locale.language.id"
                        @changeLanguage="(data) => { locale.language.id = Number(data[1]), locale.language.name = data[0].label }" />
                </div>
            </div>

            <!-- Uncomment the code below to add tab -->
            <div class="relative flex z-50 select-none justify-center items-center px-8 gap-x-1 cursor-pointer"
                :class="[isTabActive == 'activeUsers' ? 'bg-gray-600' : 'bg-gray-800 hover:bg-gray-700']"
                @click="isTabActive == 'activeUsers' ? isTabActive = !isTabActive : isTabActive = 'activeUsers'"
            >
                <div class="text-sm text-gray-400 flex items-center gap-x-1"><div class="ring-1 h-2 aspect-square rounded-full" :class="[activities.length > 0 ? 'bg-green-400 ring-green-600' : 'bg-gray-400 ring-gray-600']" /> Active Users ({{ activities.length }})</div>
                <div class="absolute bottom-5 right-0 w-40 min-w-min overflow-hidden rounded-t" :class="[isTabActive == 'activeUsers' ? 'h-max' : 'h-0']" >
                    <FooterTabActiveUsers :active="isTabActive == 'activeUsers'" :data="activities" />
                </div>
            </div>

        </div>
    </footer>
</template>



