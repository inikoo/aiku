<script setup lang='ts'>
import Image from '../Image.vue'
import { useFormatTime } from '@/Composables/useFormatTime'
import { Image as ImageTS } from '@/types/Image'
import { faAndroid } from "@fortawesome/free-brands-svg-icons"
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'

library.add(faAndroid)

const props = defineProps<{
    data: {
        id: number
        username: string
        avatar?: ImageTS
        email: string
        about: string
        status: {
            tooltip: string
            icon: string
            class: string
        }
        parent_type: string
        contact_name: string
        created_at: string
        roles: string[]
        permissions: string[]
    }
}>()
</script>

<template>
    <div class="px-6 py-6 ">
        <div class="ring-1 ring-gray-300 shadow rounded-2xl p-6 grid grid-cols-9">
            <div class="col-span-2 aspect-square rounded-full overflow-hidden md:h-56" :src="'person.imageUrl'" alt="">
                <Image :src="data?.avatar" />
            </div>

            <div class="col-span-4">
                <div class="flex items-end gap-x-2">
                    <div class="font-semibold text-2xl">{{ data?.contact_name }}</div>
                    <div class="text-gray-400">
                        #{{data?.id}} {{ data?.username }}
                    </div>

                </div>
                <div class="mt-4">
                    <div class="font-medium">
                        Description
                    </div>

                    <div v-if="data?.about" class="text-gray-500">
                        {{ data?.about }}
                    </div>

                    <div v-else class="text-gray-400 italic">
                        {{ 'No description yet' }}
                    </div>
                </div>
            </div>

            <!-- Section: Contact Information -->
            <div class="border-l border-gray-300 pl-6 col-span-3 space-y-3">
                <div class="font-semibold">Contact Information</div>
                
                <div class="space-y-2">
                    <div class="grid grid-cols-3 gap-x-5 text-sm">
                        <div class="text-gray-400">
                            Status
                        </div>
                        <div class="col-span-2 font-medium capitalize">
                            {{ data?.status?.tooltip }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-x-5 text-sm">
                        <div class="text-gray-400">
                            User type
                        </div>
                        <div class="col-span-2 font-medium capitalize">
                            {{ data?.parent_type }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-x-5 text-sm">
                        <div class="text-gray-400">
                            Member since
                        </div>
                        <div class="col-span-2 font-medium">
                            {{ useFormatTime(data?.created_at) }}
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-x-5 text-sm">
                        <div class="text-gray-400">
                            Email
                        </div>
                        <div class="col-span-2 font-medium">
                            {{ data?.email }}
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-x-5 text-sm">
                        <div class="text-gray-400">
                            Roles
                        </div>
                        <div class="col-span-2 font-medium">
                            {{ data?.roles?.length }} roles
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-x-5 text-sm">
                        <div class="text-gray-400">
                            Permissions
                        </div>
                        <div class="col-span-2 font-medium">
                            {{ data?.permissions?.length }} access
                        </div>
                    </div>
                </div>
            </div>
        </div>   

        <div class="ring-1 ring-gray-300 shadow rounded-2xl p-6 mt-2 w-1/3">
            <div class="font-semibold">Download  App</div>
            <a href="https://github.com/inikoo/maya/releases/tag/v0.0.6"  target="_blank"  class="flex items-end gap-x-2 mt-2">
                <font-awesome-icon :icon="['fab', 'android']" />
                    <div class="text-gray-400 text-sm leading-4">
                        Android
                    </div>
                </a>
        </div>
            

    </div>

    
</template>