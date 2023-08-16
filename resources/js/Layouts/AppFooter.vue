<script setup lang="ts">
import { ref, Ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { trans, loadLanguageAsync, getActiveLanguage } from 'laravel-vue-i18n'
import FooterTab from '@/Components/Footer/FooterTab.vue'
import { useLocaleStore } from "@/Stores/locale"
import { useLayoutStore } from "@/Stores/layout"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
const locale = useLocaleStore()
const layout = useLayoutStore()

import { useDatabaseList, useDatabaseObject } from "vuefire"
import { getDatabase, ref as dbRef } from "firebase/database"
import { initializeApp } from "firebase/app"

const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    databaseURL: import.meta.env.VITE_FIREBASE_DATABASE_URL,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: import.meta.env.VITE_FIREBASE_MSG_SENDER_ID,
    appId: import.meta.env.VITE_FIREBASE_APP_ID,
    measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID
};

const firebaseApp = initializeApp(firebaseConfig);
const db = getDatabase(firebaseApp)
const activities = useDatabaseList(dbRef(db, layout.tenant.code))


const isTabActive: Ref<boolean | string> = ref(false)

const form = useForm({
    language_id: null,
})

</script>

<template>
    <footer class="z-20 fixed w-screen bg-gray-800 bottom-0 right-0  text-white">
        <!-- Helper: Outer background (close popup purpose) -->
        <div class="fixed z-40 right-0 top-0 bg-transparent w-screen h-screen" @click="isTabActive = !isTabActive"
            :class="[isTabActive ? '' : 'hidden']" />
        <div class="flex justify-between">
            <!-- Left Section -->
            <div class="pl-4 flex items-center gap-x-1.5 py-1">
                <img src="@/../art/logo/svg/logo-no-background.svg" alt="Aiku" class="h-4">
            </div>

            <div class="flex items-end flex-row-reverse text-sm">
                <!-- Tab: Active Users -->
                <div class="relative h-full flex z-50 select-none justify-center items-center px-8 gap-x-1 cursor-pointer"
                    :class="[isTabActive == 'activeUsers' ? 'bg-gray-600' : 'bg-gray-800']"
                    @click="isTabActive == 'activeUsers' ? isTabActive = !isTabActive : isTabActive = 'activeUsers'"
                >
                    <div class="text-xs text-gray-300 flex items-center gap-x-1">
                        <div class="ring-1 h-2 aspect-square rounded-full" :class="[activities.length > 0 ? 'bg-green-400 ring-green-600' : 'bg-gray-400 ring-gray-600']" />
                        <span v-if="activities.length > 0">Active Users ({{ activities.length }})</span>
                        <span v-else>No active user</span>
                    </div>

                    <FooterTab @pinTab="() => isTabActive = false" v-if="isTabActive == 'activeUsers'" :tabName="`activeUsers`">
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
                    @click="isTabActive = 'language'"
                >
                    <FontAwesomeIcon icon="fal fa-language" class="text-xs mr-1 h-5 text-gray-300" />
                    <div class="h-full font-extralight text-xs flex items-center leading-none text-gray-300">
                        {{ locale.language.name }}
                    </div>
                    <FooterTab @pinTab="() => isTabActive = false" v-if="isTabActive === 'language'" :tabName="`language`">
                        <template #default>
                            <form
                                @submit.prevent="form.patch(route('models.profile.update'))"
                                v-for="(option, index) in locale.languageOptions"
                                :class="[ option.id == locale.language.id ? 'bg-gray-600 hover:bg-gray-500' : '', 'grid hover:bg-gray-700 ']"
                            >
                                <button @click="form.language_id = option.id, locale.language = option" type="submit" class="py-1.5">
                                    {{ option.name }}
                                </button>
                            </form>
                        </template>
                    </FooterTab>
                </div>
            </div>

        </div>
    </footer>
</template>



