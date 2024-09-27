<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'
import { Link, router } from '@inertiajs/vue3'
import { inject, ref } from 'vue'
import SearchBar from "@/Components/SearchBar.vue"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Image from '@/Components/Image.vue'


const showSearchDialog = ref(false)

const layout = inject('layout')
</script>

<template>
    <!-- Avatar Group -->
    <div class="flex justify-between gap-x-2">
        <div class="flex">
            <!-- Avatar Button -->
            <Link :href="route('retina.profile.show')"
                id="avatar-thumbnail"
                class="pl-3 pr-1 flex gap-x-2 items-center rounded-full"
                :class="layout?.currentRoute.includes(urlPrefix + 'profile.show') ? 'bg-gray-200 ring-1 ring-gray-300' : 'hover:bg-gray-200'"
            >
                <div class="text-gray-700 text-lg">{{ layout?.customer?.company_name }}</div>
                <span class="sr-only">{{ trans("Open user menu") }}</span>
                <div class="h-8 aspect-square rounded-full overflow-hidden border border-gray-300">
                    <Image v-if="layout.user.avatar_thumbnail" :src="layout.user.avatar_thumbnail" alt="" />
                    <img v-else src="/retina-default-user.svg" alt="Retina default avatar" class="p-0.5">
                </div>
            </Link>
            
            <!-- Button: Search -->
            <button @click="showSearchDialog = !showSearchDialog" id="search"
                class="h-8 w-8 grid items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                <span class="sr-only">{{ trans("Search") }}</span>
                <FontAwesomeIcon aria-hidden="true" icon="fa-regular fa-search" size="lg" />
                <SearchBar :isOpen="showSearchDialog" @close="(e) => showSearchDialog = e" />
            </button>

            <!-- Button: Notifications -->
            <!-- <button type="button"
                    class="h-8 w-8 grid items-center justify-center rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500">
                <span class="sr-only">{{ trans("View notifications") }}</span>
                <FontAwesomeIcon aria-hidden="true" icon="fa-regular fa-bell" size="lg" />
            </button> -->
        </div>

        
    </div>
</template>