<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Tue, 19 Sep 2023 15:25:16 Malaysia Time, Pantai Lembeng, Bali, Indonesia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { ref, Ref } from 'vue'
import FooterActiveUsers from '@/Components/Footer/FooterActiveUsers.vue'
import FooterLanguage from '@/Components/Footer/FooterLanguage.vue'
import { faHeart, faComputerClassic } from '@fas'
import { faDiscord } from '@fortawesome/free-brands-svg-icons'
import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

const isTabActive: Ref<boolean | string> = ref(false)
import { trans } from "laravel-vue-i18n";

library.add(faHeart, faComputerClassic, faDiscord)

</script>

<template>
    <footer class="z-20 fixed w-screen bottom-0 right-0  text-white bg-black">
        <!-- Helper: Outer background (close popup purpose) -->
        <div class="fixed z-40 right-0 top-0 bg-transparent w-screen h-screen" @click="isTabActive = !isTabActive"
            :class="[isTabActive ? '' : 'hidden']" />

        <div class="flex justify-between">
            <!-- Left: Logo Section -->
            <div class="pl-4 flex items-center gap-x-1.5 py-1 text-slate-400">
                <span class="font-normal leading-none">Aiku</span>
                <img class="h-3 select-none hidden lg:inline pl-1 pr-1" src="/art/logo-yellow.svg"  alt="aiku"/>
                <span class="text-xs hidden lg:inline">
                    {{ trans('Made with') }}
                    <FontAwesomeIcon icon='fas fa-heart' class="text-red-500 mx-1" aria-hidden='true' />
                    {{ trans('and') }}
                    <FontAwesomeIcon icon='fas fa-computer-classic' class="mx-1" aria-hidden='true' /> {{ 'in Bali' }}
                </span>
            </div>

            <!-- Discord -->
            <!-- <div class="pl-4 flex items-center gap-x-1.5">
                <a href="https://discord.gg/C7bCmMaTxP" target="_blank">
                    <span class="text-slate-400	 text-xs">
                        <FontAwesomeIcon :icon="['fab', 'discord']" class="text-white mx-1" aria-hidden='true' />
                        <span class="hidden sm:inline">{{ trans("Join our community") }}</span> <span
                            class="hidden lg:inline">{{ trans('announcements/feedback/wishlists') }}</span>
                    </span>
                </a>
            </div> -->

            <!-- Right: Tab Section -->
            <div class="flex items-end flex-row-reverse text-sm">
                <FooterActiveUsers :isTabActive="isTabActive" @isTabActive="(value: any) => isTabActive = value" />
                <FooterLanguage :isTabActive="isTabActive" @isTabActive="(value: any) => isTabActive = value" />
            </div>
        </div>
    </footer>
</template>
