<script setup lang='ts'>
import { useFormatTime, useRangeFromNow } from '@/Composables/useFormatTime'

const props = defineProps<{
    dataFeeds: { 
        [key: string]: {
            name?: string
            label: string
            description?: string
            comment?: string
        }
    }
}>()


</script>

<template>
    <!-- <pre>{{dataFeeds}}</pre> -->
    <!-- Vertical -->
    <ul role="list" class="space-y-6 text-gray-600">
        <li v-for="(feed, feedDate, feedIdx) in dataFeeds" :key="feedIdx" class="relative flex gap-x-4">
            <!-- Step: the line -->
            <div :class="[feedIdx === dataFeeds.length - 1 ? 'h-6' : '-bottom-6', 'absolute left-0 top-0 flex w-6 justify-center']">
                <div class="w-px bg-gray-200" />
            </div>
            
            <!-- Step: the bullet -->
            <div class="relative flex h-6 w-6 flex-none items-center justify-center">
                <div class="h-2 aspect-square rounded-full bg-gray-500 ring-1 ring-gray-300" />
            </div>

            <!-- Condition: have comment/note -->
            <div v-if="feed.comment" class="bg-gray-100 flex-auto rounded-md p-3 ring-1 ring-inset ring-gray-200">
                <div class="flex justify-between gap-x-4">
                    <div class="flex text-xs leading-5 gap-x-1">
                        <div class="font-medium">{{ feed.name }}</div>
                        <div class="capitalize text-gray-400">{{ feed.label }}</div>
                    </div>
                    <time :datetime="feedDate" class="flex-none text-xs leading-5 text-gray-500" :title="useFormatTime(feedDate)">
                        {{ useRangeFromNow(feedDate) }} ago
                    </time>
                </div>
                <p class="text-sm leading-6 text-gray-500">{{ feed.comment }}</p>
            </div>

            <!-- Condition: normal -->
            <div v-else class="flex justify-between w-full gap-x-5">
                <div class="flex flex-col items-center text-xs leading-5 gap-x-1 gap-y-1">
                    <div v-if="feed.label" class="font-semibold text-gray-600 leading-4">{{ feed.label }}</div>
                    <div class="capitalize text-gray-400 leading-4 text-xxs">{{ feed.description }}</div>
                </div>
                <div :datetime="feedDate" class="flex-none text-xs leading-5 text-gray-500" v-tooltip="useFormatTime(feedDate, {formatTime: 'hms'})">
                    {{ useRangeFromNow(feedDate) }} ago
                </div>
            </div>
        </li>
    </ul>
</template>