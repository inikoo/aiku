<template>
    <footer class="z-20 fixed w-screen bg-gray-800 bottom-0 right-0  text-white grid justify-end">
        <!-- Outer background -->
        <div class="fixed z-40 right-0 top-0 bg-gray-800/30 w-screen h-screen cursor-pointer" @click="isFooter = !isFooter"
            :class="[isFooter ? '' : 'hidden']"></div>
        <div class="flex items-end flex-row-reverse text-sm">

            <!-- Footer List -->
            <div v-for="footer in footerList"
                class="relative flex z-50 select-none justify-center w-40 min-w-min cursor-pointer hover:bg-gray-700 "
                :class="[isFooter == footer.key ? 'bg-gray-700' : 'bg-gray-800']"
                @click="isFooter == footer.key ? isFooter = !isFooter : isFooter = footer.key"
            >
                <p>{{ footer.key }}</p>
                
                <!-- Footer Active: Box -->
                <div class="absolute bottom-5 w-full overflow-hidden rounded-t"
                    :class="[isFooter == footer.key ? 'h-max' : 'h-0']"
                >
                    <FooterLanguageTab :active="isFooter == footer.key" :options="footer.options" />
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { ref } from 'vue'
import FooterLanguageTab from '@/Components/Footer/FooterLanguageTab.vue'

const footerList = ref([
    {
        'key': 'Language',
        'label': 'Change your language',
        'options': [
            'English',
            'Spanish',
            'Russians',
            'Bahasa Indonesia',
            'Chinese',
            'Japanese',
        ]
    },
    {
        'key': 'Theme',
        'label': 'Change your theme',
    },
    {
        'key': 'Status',
        'label': 'Change your status',
    },
    {
        'key': 'Profile',
        'label': 'Change your profile',
    },
])

const isFooter = ref(false)
</script>

<style scoped></style>