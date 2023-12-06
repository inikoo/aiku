<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 04 Sep 2023 10:37:14 Malaysia Time, Kuala Lumpur, Malaysia
  - Copyright (c) 2023, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import { trans } from 'laravel-vue-i18n'
import { ref, computed } from 'vue'
import { useLayoutStore } from "@/Stores/layout"
import FooterTab from '@/Components/Footer/FooterTab.vue'
import { getDataFirebase } from '@/Composables/firebase'
import { watchEffect } from 'vue'

const props = defineProps<{
    isTabActive: string | boolean
}>()

defineEmits<{
    (e: 'isTabActive', value: boolean | string): void
}>()

const layout = useLayoutStore()

const dbPath = 'customers/' + layout.user?.username + '/active_users'  // Firebase Path

const getDataCustomer = ref(getDataFirebase(dbPath))
const dataCustomer = ref()
const dataCustomerLength = ref()

const compUserOnline = computed(() => {
    return dataCustomer.value.filter((item: any) => item.loggedIn === true)
})

watchEffect(() => {
    console.log("eeeeee", getDataCustomer.value)
    dataCustomer.value = getDataCustomer.value
    dataCustomerLength.value = compUserOnline.value ? Object.keys(compUserOnline.value).length : 0
    layout.rightSidebar.activeUsers.users = dataCustomer.value
    layout.rightSidebar.activeUsers.count = dataCustomerLength.value
})

const getStatusOnline = (dataUser: any) => {
    // if stay on the same page for over than 10 minute return true
    const lastActive = new Date(dataUser.last_active)
    const currentTime = new Date()
    const timeDifference = Math.floor((currentTime - lastActive) / (1000 * 60))

    return timeDifference < 11
}



</script>

<template>
    <div class="relative h-full flex z-50 select-none justify-center items-center px-8 gap-x-1 cursor-pointer text-gray-300"
        :class="[isTabActive == 'activeUsers' ? 'bg-gray-700 text-gray-300' : 'text-gray-300 hover:bg-gray-600']"
        @click="isTabActive == 'activeUsers' ? $emit('isTabActive', !isTabActive) : $emit('isTabActive', 'activeUsers')">
        <div class="relative text-xs flex items-center gap-x-1">
            <div class="ring-1 h-2 aspect-square rounded-full"
                :class="[dataCustomerLength > 0 ? 'animate-pulse bg-green-400 ring-green-600' : 'bg-gray-400 ring-gray-600']" />
            <span class="">{{ trans('Active Users') }} ({{ dataCustomerLength ?? 0 }})</span>
        </div>
        <FooterTab @pinTab="() => $emit('isTabActive', false)" v-if="isTabActive == 'activeUsers'" :tabName="`activeUsers`">
            <template #default>
                <div v-if="dataCustomerLength" v-for="(dataUser, index) in compUserOnline"
                    class="flex justify-start py-1 px-2 gap-x-1.5 hover:bg-gray-700 cursor-default">
                    <!-- <img :src="`/media/${user.user.avatar_thumbnail}`" :alt="user.user.contact_name" srcset="" class="h-4 rounded-full shadow"> -->
                    <span class="font-semibold" :class="[getStatusOnline(dataUser) ? 'text-gray-100' : 'text-gray-400']">{{
                        dataUser.id }}</span> -
                    <span v-if="dataUser.loggedIn" class=""
                        :class="[getStatusOnline(dataUser) ? 'text-gray-300' : 'text-gray-400']">{{
                            getStatusOnline(dataUser) ? dataUser.route.name : 'Away' }}</span>
                </div>
            </template>
        </FooterTab>
        <!-- {{ getDataCustomer }} -->
    </div>
</template>
