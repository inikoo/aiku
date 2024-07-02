<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 04 Sep 2023 11:19:39 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n'
import { useLayoutStore } from "@/Stores/layout"
import FooterTab from '@/Components/Footer/FooterTab.vue'
import { faBriefcase } from '@fal'
import { library } from "@fortawesome/fontawesome-svg-core"
import { useLiveUsers } from '@/Stores/active-users'
import { useTruncate } from '../../Composables/useTruncate'
import Image from '@/Components/Image.vue'
import { Link } from '@inertiajs/vue3'
import { Popover, PopoverButton, PopoverPanel } from '@headlessui/vue'
library.add(faBriefcase)

const layout = useLayoutStore()

</script>

<template>
    <Popover v-slot="{ open }" class="relative h-full">
        <PopoverButton :class="open ? 'bg-white/50 text-white' : 'hover:bg-white/25 text-gray-200'"
            class="group inline-flex items-center px-3 h-full font-medium">
            <div class="relative text-xs flex items-center gap-x-1">
                <div class="ring-1 h-2 aspect-square rounded-full"
                    :class="[useLiveUsers().count > 0 ? 'animate-pulse bg-green-400 ring-green-600' : 'bg-gray-400 ring-gray-600']" />
                <span class="">{{ trans('Active users') }} ({{ useLiveUsers().count ?? 0 }})</span>
            </div>
        </PopoverButton>

        <transition name="headlessui">
            <PopoverPanel class="absolute bottom-full right-0 z-10 sm:px-0">
                <FooterTab tabName="activeUsers">
                    <template #default>
                        <Link v-for="(dataUser, index) in useLiveUsers().liveUsers"
                            :href="dataUser.current_page?.url || '#'"
                            class="flex items-center py-1 px-2 gap-x-1.5 hover:bg-gray-300/10"
                            :class="dataUser.id == layout.user.id ? 'bg-gray-100/20' : ''">
                            <Image :src="dataUser.avatar_thumbnail" :alt="dataUser.username"
                                class="h-5 rounded-full shadow overflow-hidden" />
                            <p class="flex flex-col items-start">
                                <span class="capitalize text-xs font-bold">{{ dataUser?.username }}</span>

                                <Transition name="spin-to-down">
                                    <span v-if="dataUser.current_page?.label" :key="dataUser.current_page?.label" class="capitalize opacity-70">
                                        {{ useTruncate(dataUser?.current_page?.label, 17) }}
                                    </span>
                                    <span v-else class="capitalize text-gray-500 italic opacity-60">Unknown</span>
                                </Transition>
                            </p>
                            <!-- <span v-if="dataUser.loggedIn" class="text-gray-800">{{ dataUser.route?.name ? trans(dataUser.route.label ?? '') : '' }}</span>
                                    <span v-else-if="getAwayStatus(dataUser.last_active)" class="text-gray-800">{{ getAwayStatus(dataUser.last_active) ? 'Away' : '' }}</span> -->
                            <!-- <span v-if="dataUser.route.subject" class="capitalize text-gray-300">{{ dataUser.route.subject }}</span> -->
                        </Link>
                    </template>
                </FooterTab>
            </PopoverPanel>
        </transition>
    </Popover>
</template>
