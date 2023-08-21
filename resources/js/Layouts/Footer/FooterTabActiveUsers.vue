<script setup lang="ts">
import { trans } from 'laravel-vue-i18n'
import { ref } from 'vue'
import { useLayoutStore } from "@/Stores/layout"
import FooterTab from '@/Components/Footer/FooterTab.vue'

import { getDataFirebase } from '@/Composables/firebase'
import { watchEffect } from 'vue'

const props = defineProps<{
    isTabActive: string | boolean
    appScope: string
}>()

defineEmits<{
    (e: 'isTabActive'): void
}>()

const layout = useLayoutStore()




const getDataTenants = ref(getDataFirebase(props.appScope))

console.log(getDataTenants)

const dataTenants = ref()
const dataTenant = ref()
const dataTenantLength = ref()

watchEffect(() => {
    dataTenants.value = getDataTenants.value
    dataTenant.value = dataTenants.value.find(obj => obj.id === layout.tenant.code) ?? dataTenants.value[0]
    dataTenantLength.value = dataTenant.value ? Object.keys(dataTenant.value).length : 0
})

</script>

<template>
    <!-- {{ dataTenant }} -->
    <div class="relative h-full flex z-50 select-none justify-center items-center px-8 gap-x-1 cursor-pointer text-gray-300"
        :class="[isTabActive == 'activeUsers' ? 'bg-gray-700' : '']"
        @click="isTabActive == 'activeUsers' ? $emit('isTabActive', !isTabActive) : $emit('isTabActive', 'activeUsers')"
    >
        <div class="relative text-xs flex items-center gap-x-1">
            <div class="ring-1 h-2 aspect-square rounded-full" :class="[dataTenantLength > 0 ? 'animate-pulse bg-green-400 ring-green-600' : 'bg-gray-400 ring-gray-600']" />
            <span class="">{{ trans('Active Users') }} ({{ dataTenantLength ?? 0 }})</span>
        </div>

        <FooterTab @pinTab="() => $emit('isTabActive', false)" v-if="isTabActive == 'activeUsers'" :tabName="`activeUsers`">
            <template #default>
                <div v-if="dataTenantLength" v-for="(dataUser, userName) in dataTenant.active_users ?? dataTenant" class="flex justify-start py-1 px-2 gap-x-1.5 hover:bg-gray-700 cursor-default">
                    <!-- <img :src="`/media/${user.user.avatar_thumbnail}`" :alt="user.user.contact_name" srcset="" class="h-4 rounded-full shadow"> -->
                    <p class="text-left text-gray-100">
                        <span class="font-semibold text-gray-100">{{ userName }}</span> -
                        <span class="capitalize text-gray-300">{{ dataUser.route.module }}</span>
                    </p>
                </div>
            </template>
        </FooterTab>
    </div>
</template>
