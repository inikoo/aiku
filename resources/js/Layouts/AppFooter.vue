<script setup lang="ts">
import { ref } from 'vue'
import FooterTabLanguage from '@/Components/Footer/FooterTabLanguage.vue'
import { useLocaleStore } from "@/Stores/locale";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

const locale = useLocaleStore()
// const tabLanguage = ref({
//     'key': 'xx',
//     'label': 'Change your language',
//     'options': [
//         'English (EN-US)',
//         'Spanish (SP)',
//         'Russians (RU)',
//         'Bahasa Indonesia (ID)',
//         'Chinese (CN)',
//         'Japanese (JP)',
//     ]
// },)

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
                    <FooterTabLanguage :active="isTabActive == 'language'" :options="locale.languageOptions" :selected="locale.language.id"
                        @changeLanguage="(data) => { locale.language.id = Number(data[1]), locale.language.name = data[0].label }"
                        
                    />
                </div>
            </div>

            <!-- Uncomment the code below to add tab -->
            <!-- <div class="relative flex z-50 select-none justify-center px-8  cursor-pointer"
                :class="[isTabActive == tabLanguage.key ? 'bg-gray-600' : 'bg-gray-800 hover:bg-gray-700']"
                @click="isTabActive == tabLanguage.key ? isTabActive = !isTabActive : isTabActive = tabLanguage.key"
            >
                <FontAwesomeIcon icon="fal fa-language" class="mr-1 h-5 text-gray-400"></FontAwesomeIcon>
                <span class="font-thin text-sm">{{ locale.language.code }}</span>
            
                <div class="absolute bottom-5 right-0 w-40 min-w-min overflow-hidden rounded-t"
                    :class="[isTabActive == tabLanguage.key ? 'h-max' : 'h-0']"
                >
                    <FooterTabLanguage :active="isTabActive == tabLanguage.key" :options="locale.languageOptions" />
                </div>
            </div> -->

        </div>
    </footer>
</template>



