<template>
    <div class="w-full min-w-max bg-gray-100 shadow-lg flex-row items-start text-gray-800 text-xs cursor-default" >
            <!-- <pre>{{ data }}</pre> -->
        <div class="flex flex-col justify-center text-center pb-3 gap-y-1">
            <div v-for="(option, index) in data" class="flex justify-start py-1 px-2 gap-x-1.5 hover:bg-gray-200">
                <img :src="`http://aw.aiku.test/media/group/${option.user.avatar_id}`" :alt="option.user.contact_name" srcset="" class="h-4 rounded-full shadow">
                <p class="text-left text-gray-600">
                    <span class="font-semibold">{{ option.user.contact_name }}</span> 
                    <span class="text-gray-400"> ({{ option.user.username }})</span> - 
                    <span class="capitalize">{{ option.route.module }}</span></p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">

const props = defineProps<{
    data: [{
        is_active: boolean
        last_active: string
        route: {
            module: string
            name: string
        }
        user: {
            avatar_id: number
            contact_name: string
            username: string
        }
    }]
}>()

</script>

<style lang="scss" scoped></style>