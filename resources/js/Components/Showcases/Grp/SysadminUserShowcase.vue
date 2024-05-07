<script setup lang='ts'>
import Image from '@/Components/Image.vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { useLiveUsers } from '@/Stores/active-users'
import { Image as ImageTS } from '@/types/Image'
import { inject } from 'vue'

const props = defineProps<{
    tab: string
    data: {
        data: {
            id: number
            username: string
            avatar: ImageTS
            email?: string
            about?: string
            parent_type: string
            contact_name: string
            authorizedOrganisations: []
            permissions: []
            lastLogged?: Date
            loggedIn: {
                status: boolean
                section: string
            }
        }
    }
}>()

const layout = inject('layout', layoutStructure)
const activeUsers = useLiveUsers().liveUsers
console.log('qq', activeUsers)

</script>

<template>
    <div>
        <!-- <pre>{{ data }}</pre> -->
        <!-- <pre>{{ activeUsers }}</pre> -->
    </div>

    <div class="py-4 px-8">
        <div class="h-40 aspect-square rounded-full overflow-hidden shadow">
            <Image :src="data.data.avatar" />
        </div>

        <div class="flex gap-x-1">
            <div>Email:</div>
            <div class="font-semibold">{{ data.data.email || '-' }}</div>
        </div>
        <div class="flex gap-x-1">
            <div>Name:</div>
            <div class="font-semibold">{{ data.data.contact_name }}</div>
        </div>
        <div class="flex gap-x-1">
            <div>Status:</div>
            <div class="font-semibold">{{ activeUsers[data.id] ? 'Online' : 'Offline' }}</div>
        </div>
        <div class="flex gap-x-1">
            <div>Last Online:</div>
            <div class="font-semibold">{{ data.data.lastLogged || 'Never' }}</div>
        </div>
    </div>
</template>