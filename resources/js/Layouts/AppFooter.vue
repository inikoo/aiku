<script setup lang="ts">
import { ref } from 'vue'
import FooterTabLanguage from '@/Components/Footer/FooterTabLanguage.vue'
import { useLocaleStore } from "@/Stores/locale";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

const locale = useLocaleStore();

const tabLanguage = ref({
    'key': 'xx',
    'label': 'Change your language',
    'options': [
        'English (EN-US)',
        'Spanish (SP)',
        'Russians (RU)',
        'Bahasa Indonesia (ID)',
        'Chinese (CN)',
        'Japanese (JP)',
    ]
},)

const isFooter = ref(false)
</script>

<template>
    <footer class="z-20 fixed w-screen bg-gray-800 bottom-0 right-0  text-white grid justify-end">
        <!-- Outer background -->
        <div class="fixed z-40 right-0 top-0 bg-gray-800/30 w-screen h-screen" @click="isFooter = !isFooter"
            :class="[isFooter ? '' : 'hidden']"></div>
        <div class="flex items-end flex-row-reverse text-sm">
            <!-- Tab: Language -->
            <div
                class="relative flex z-50 select-none justify-center w-40 min-w-min cursor-pointer"
                :class="[isFooter == tabLanguage.key ? 'bg-gray-600' : 'bg-gray-800 hover:bg-gray-700']"
                @click="isFooter == tabLanguage.key ? isFooter = !isFooter : isFooter = tabLanguage.key"
            >
                <font-awesome-icon icon="fal fa-language" class="mr-1 h-5 text-gray-400"></font-awesome-icon>
                <span class="font-thin text-sm">{{ locale.language.code }}</span>
                <div class="absolute bottom-5 w-full overflow-hidden rounded-t"
                    :class="[isFooter == tabLanguage.key ? 'h-max' : 'h-0']"
                >
                    <FooterTabLanguage :active="isFooter == tabLanguage.key" :options="tabLanguage.options" />
                </div>
            </div>

        </div>
    </footer>
</template>



