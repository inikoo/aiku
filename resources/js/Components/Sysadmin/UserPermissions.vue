<script setup lang='ts'>
import Tag from '@/Components/Tag.vue'

const props = defineProps<{
    data: string[]
}>()
</script>

<template>
    <div class="px-6 py-4">
        <div class="grid grid-cols-2">
            <div>
                <div class="font-semibold text-lg">
                    Permissions ({{ data.length }}):
                </div>
                
                <div class="flex gap-x-2.5 gap-y-2 flex-wrap">
                    <Tag v-for="role in data" :label="role" size="base">
                        <template #label>
                            <div class="whitespace-nowrap">{{ role }}</div>
                        </template>
                    </Tag>
                </div>
            </div>

        </div>
    </div>
</template>