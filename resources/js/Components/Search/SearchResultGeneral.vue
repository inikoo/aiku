<script setup lang='ts'>
import Image from '@/Components/Image.vue'

const props = defineProps<{
    activeOption: any
}>()
</script>

<template>
    <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm text-gray-600">
        <div class="flex-none p-6 text-center">
            <div class="bg-gray-100 ring-2 ring-gray-300 shadow mx-auto h-16 w-16 rounded-full overflow-hidden">
                <Image :src="activeOption.model.avatar" :alt="activeOption.model.code" />
            </div>
            <h2 class="mt-3 font-semibold text-gray-600">
                {{ activeOption.model.contact_name }}
            </h2>
        </div>

        <dt class="col-end-1 font-semibold text-gray-600">Email:</dt>
        <dd>{{ activeOption.model.email }}</dd>
        <dt class="col-end-1 font-semibold text-gray-600">Roles:</dt>
        <dd>{{ activeOption.model.roles }}</dd>
    </dl>
</template>