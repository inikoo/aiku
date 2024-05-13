<script setup lang='ts'>
import Image from '@/Components/Image.vue'
import { layoutStructure } from '@/Composables/useLayoutStructure'
import { useLiveUsers } from '@/Stores/active-users'
import { Image as ImageTS } from '@/types/Image'
import { inject } from 'vue'
import Tag from '@/Components/Tag.vue'
import { useFormatTime } from '@/Composables/useFormatTime'

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
console.log('qq', props)

</script>

<template>
    <div class="flex py-4 px-8">
        <div class="h-40 aspect-square rounded-full overflow-hidden shadow m-5">
            <Image :src="data.data.avatar" />
        </div>

        <div class="w-full">
            <dl class="grid grid-cols-1 sm:grid-cols-2">

                <div class="px-4 py-6 sm:col-span-1 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Email :</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:mt-2">{{ data.data.email || '-' }}</dd>
                </div>


                <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Name :</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:mt-2">{{ data.data.contact_name }}</dd>
                </div>
            </dl>

            <div class="mt-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2">

                    <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">Status :</dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:mt-2">  <Tag :label="activeUsers[data.data.id] ? 'Online' : 'Offline'" :theme="activeUsers[data.data.id] ? 3 : ''"></Tag></dd>
                    </div>

                    <div class="border-t border-gray-100 px-4 py-6 sm:col-span-1 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">Last Active :</dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:mt-2">{{ activeUsers[data.data.id]?.last_active ? useFormatTime(activeUsers[data.data.id].last_active) : 'Never' }}
                        </dd>
                    </div>

                    <div class="border-t border-gray-100 px-4 py-6 sm:col-span-2 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">Authorized Organisations : </dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:mt-2"
                            style="display: flex; flex-wrap: wrap;">
                            <div v-for="item of data.data.authorizedOrganisations" class="m-1">
                                <Tag :label="item.name"></Tag>
                            </div>
                        </dd>
                    </div>

                </dl>
            </div>
        </div>
    </div>
</template>
